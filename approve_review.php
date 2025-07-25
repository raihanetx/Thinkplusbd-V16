<?php
function approve_review($review_id) {
    if (!empty($review_id)) {
        $reviews_file_path = __DIR__ . '/reviews.json';
        if (file_exists($reviews_file_path)) {
            $reviews_json = file_get_contents($reviews_file_path);
            $reviews = json_decode($reviews_json, true);

            // Find the review by its ID and update its status
            $review_found = false;
            foreach ($reviews as $key => &$review) {
                if (isset($review['id']) && (string)$review['id'] == $review_id) {
                    $reviews[$key]['status'] = 'approved';
                    $review_found = true;
                    break;
                }
            }

            if ($review_found) {
                file_put_contents($reviews_file_path, json_encode($reviews, JSON_PRETTY_PRINT));
                return ['success' => true];
            }
        }
    }
    return ['success' => false];
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$review_id = isset($input['id']) ? (string)$input['id'] : '';
echo json_encode(approve_review($review_id));
?>
