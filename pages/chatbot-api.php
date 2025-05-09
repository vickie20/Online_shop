<?php
header('Content-Type: application/json');

// Simple chatbot responses (in a real application, you might use NLP or a more complex system)
$responses = [
    'greeting' => [
        'Hello! How can I assist you with your shopping today?',
        'Hi there! What can I help you find?',
        'Welcome to ShopAll! How may I help you?'
    ],
    'delivery' => [
        'We offer free shipping on orders over $50. Delivery typically takes 3-5 business days.',
        'Standard shipping is $5.99 and takes 3-5 business days. Express shipping is available for $12.99 (1-2 business days).'
    ],
    'returns' => [
        'We have a 30-day return policy. Items must be unused and in original packaging.',
        'You can return items within 30 days of delivery. Please visit our Returns page for instructions.'
    ],
    'products' => [
        'You can browse our products by category or use the search function. Is there a specific product you\'re looking for?',
        'We offer a wide range of products. Could you tell me more about what you\'re interested in?'
    ],
    'contact' => [
        'You can contact our customer service team at support@shopall.com or call us at (123) 456-7890.',
        'Our customer service is available Monday-Friday, 9am-5pm EST at (123) 456-7890.'
    ],
    'default' => [
        'I\'m sorry, I didn\'t understand that. Could you please rephrase your question?',
        'I\'m still learning! Could you ask that in a different way?',
        'I\'m not sure I understand. Can you provide more details?'
    ]
];

// Get the user message from POST data
$userMessage = strtolower(trim($_POST['message'] ?? ''));

// Determine the appropriate response
$response = '';
$responseType = 'default';

if (empty($userMessage)) {
    $response = 'Hello! How can I assist you today?';
} else {
    if (strpos($userMessage, 'hello') !== false || strpos($userMessage, 'hi') !== false) {
        $responseType = 'greeting';
    } elseif (strpos($userMessage, 'delivery') !== false || strpos($userMessage, 'shipping') !== false) {
        $responseType = 'delivery';
    } elseif (strpos($userMessage, 'return') !== false || strpos($userMessage, 'refund') !== false) {
        $responseType = 'returns';
    } elseif (strpos($userMessage, 'product') !== false || strpos($userMessage, 'item') !== false) {
        $responseType = 'products';
    } elseif (strpos($userMessage, 'contact') !== false || strpos($userMessage, 'help') !== false) {
        $responseType = 'contact';
    }
    
    // Select a random response from the appropriate category
    $possibleResponses = $responses[$responseType];
    $response = $possibleResponses[array_rand($possibleResponses)];
}

// Return the response as JSON
echo json_encode([
    'response' => $response
]);