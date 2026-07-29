<?php
session_start();
header('Content-Type: application/json');

include('includes/config.php');
include('includes/chatbot_config.php');
include('includes/chatbot_tools.php');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['reset']) && $input['reset']) {
    $_SESSION['chatbot_history'] = [];
    echo json_encode(['ok' => true]);
    exit;
}

$userMessage = isset($input['message']) ? trim($input['message']) : '';

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required.']);
    exit;
}

if (mb_strlen($userMessage) > 2000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is too long.']);
    exit;
}

if (!isset($_SESSION['chatbot_history']) || !is_array($_SESSION['chatbot_history'])) {
    $_SESSION['chatbot_history'] = [];
}

$loggedIn = !empty($_SESSION['login']);

$systemPrompt = "You are the friendly virtual assistant for " . CHATBOT_SITE_NAME . ", an online car rental booking website. "
    . "Help visitors with questions about renting a car, how the booking process works, vehicle types, pricing, and general support. "
    . "Key facts about this site: "
    . "Users browse cars on the 'Car Listing' page or search by keyword/brand/fuel type. "
    . "Each car has its own details page with specs, accessories, and a booking form — booking requires being logged in. "
    . "There is a 'Smart Car Finder' page that uses an algorithm to recommend cars based on price, availability, and preferences. "
    . "Users can register/login via the modal in the top navigation, manage bookings under 'My Booking', and leave testimonials once logged in. "
    . "Prices are shown in NRS (Nepalese Rupees) per day. "
    . "Keep replies concise, warm, and helpful — a few sentences unless more detail is clearly needed. "
    . "STRICT SCOPE RULE: You only discuss this car rental website — its cars, bookings, pricing, account features, and related "
    . "customer support. You do not have general world knowledge and must not answer questions outside this scope, such as sports "
    . "results, news, celebrities, coding help, math problems, trivia, or any other unrelated topic — even if you know the answer. "
    . "For any off-topic question, do not answer it at all (do not mention the topic or acknowledge you could answer it). Instead, "
    . "briefly and politely say that's outside what you can help with here, and steer the conversation back to cars, bookings, or "
    . "this site. Keep the redirect to one short sentence. "
    . "\n\nBOOKING CAPABILITY: You can actually search cars, check login status, create a real booking, and list a user's real bookings "
    . "using the provided tools — you are not just describing the site, you can perform these actions directly. Rules: "
    . "(1) Always call check_login_status before create_booking or list_my_bookings — never assume login state. "
    . "(2) The current session's login status has already been checked once for you: user is currently " . ($loggedIn ? "LOGGED IN" : "NOT LOGGED IN") . ". "
    . "If not logged in and the user wants to book or see bookings, tell them to log in or register first via the Login/Register link in the top navigation — do not call create_booking or list_my_bookings in that case. "
    . "(3) Before calling search_cars, ask the user what they're looking for only if they haven't said anything relevant yet; otherwise search directly. "
    . "(4) Never invent car ids, prices, or availability — always get them from a tool result. "
    . "(5) Before calling create_booking, clearly confirm the exact car, from date, and to date with the user in your previous message, unless they already stated all three explicitly. "
    . "(6) When presenting search results or a booking list, format it as a clean line-per-item list (not a paragraph), e.g. 'BrandName Title — NRS price/day, fuel, seats' or 'Booking #number — car, from→to, status'. "
    . "(7) After a successful create_booking, tell the user their booking number and that it is pending confirmation.";

$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach ($_SESSION['chatbot_history'] as $turn) {
    $messages[] = $turn;
}
$messages[] = ['role' => 'user', 'content' => $userMessage];

$tools = chatbotToolDefinitions();

function callOpenRouter($messages, $tools)
{
    $payload = json_encode([
        'model' => OPENROUTER_MODEL,
        'messages' => $messages,
        'tools' => $tools,
        'tool_choice' => 'auto',
        'max_tokens' => 600,
        'temperature' => 0.7,
    ]);

    $ch = curl_init(OPENROUTER_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'Content-Type: application/json',
            'HTTP-Referer: ' . CHATBOT_SITE_URL,
            'X-Title: ' . CHATBOT_SITE_NAME,
        ],
    ]);

    $response = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrno) {
        return ['ok' => false, 'error' => 'Could not reach the assistant service. Please try again shortly.'];
    }

    $decoded = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300 || !isset($decoded['choices'][0]['message'])) {
        $detail = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unexpected response from the assistant service.';
        return ['ok' => false, 'error' => $detail];
    }

    return ['ok' => true, 'message' => $decoded['choices'][0]['message']];
}

$finalReply = null;
$maxRounds = 4;

for ($round = 0; $round < $maxRounds; $round++) {
    $result = callOpenRouter($messages, $tools);

    if (!$result['ok']) {
        http_response_code(502);
        echo json_encode(['error' => $result['error']]);
        exit;
    }

    $assistantMessage = $result['message'];
    $toolCalls = isset($assistantMessage['tool_calls']) ? $assistantMessage['tool_calls'] : null;

    if (!$toolCalls) {
        $finalReply = trim((string) ($assistantMessage['content'] ?? ''));
        break;
    }

    // Record the assistant's tool-call request, then execute each tool and feed results back.
    $messages[] = $assistantMessage;

    foreach ($toolCalls as $call) {
        $toolName = $call['function']['name'] ?? '';
        $rawArgs = $call['function']['arguments'] ?? '{}';
        $args = json_decode($rawArgs, true);
        if (!is_array($args)) {
            $args = [];
        }

        $toolResult = chatbotExecuteTool($toolName, $args, $dbh);

        $messages[] = [
            'role' => 'tool',
            'tool_call_id' => $call['id'] ?? '',
            'content' => json_encode($toolResult),
        ];
    }
}

if ($finalReply === null || $finalReply === '') {
    $finalReply = "Sorry, I couldn't finish that just now. Could you try rephrasing or asking again?";
}

$_SESSION['chatbot_history'][] = ['role' => 'user', 'content' => $userMessage];
$_SESSION['chatbot_history'][] = ['role' => 'assistant', 'content' => $finalReply];

// Keep only the last 10 turns (20 messages) to bound session size and token usage.
if (count($_SESSION['chatbot_history']) > 20) {
    $_SESSION['chatbot_history'] = array_slice($_SESSION['chatbot_history'], -20);
}

echo json_encode(['reply' => $finalReply]);
