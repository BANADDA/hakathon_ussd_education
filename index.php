<?php

// Function to display the welcome message
function welcomeMessage() {
    return "CON Welcome to the Quiz Platform!\n
    Please enter your student number:";
}

// Function to display the subjects menu
function subjectsMenu() {
    return "CON Select the subject:\n
    1. English\n
    2. SST\n
    3. Science";
}

// Function to display the quiz menu
function quizMenu() {
    return "CON Quiz Menu:\n
    1. Start Quiz\n
    0. Quit";
}

// Function to get the questions, answer options, and correct answers for each subject
function getSubjectQuestions($subject) {
    // Add questions, answers, and correct options for each subject
    $questions = array(
        "English" => array(
            "Question 1: What is the capital of France?" => array(
                "1. London",
                "2. Paris",
                "3. Berlin",
                "4. Rome",
                "5. Madrid"
            ),
            "Question 2: Who wrote the play 'Romeo and Juliet'?" => array(
                "1. William Shakespeare",
                "2. Charles Dickens",
                "3. Jane Austen",
                "4. Mark Twain",
                "5. Oscar Wilde"
            ),
            "Question 3: What is the past tense of 'run'?" => array(
                "1. Runned",
                "2. Ranned",
                "3. Ran",
                "4. Running",
                "5. Run"
            ),
            "Question 4: What is the opposite of 'hot'?" => array(
                "1. Warm",
                "2. Cold",
                "3. Spicy",
                "4. Burning",
                "5. Chilly"
            ),
            "Question 5: What is the plural of 'child'?" => array(
                "1. Children",
                "2. Childs",
                "3. Childies",
                "4. Childen",
                "5. Childs"
            )
        ),
        "SST" => array(
            "Question 1: What is the largest continent?" => array(
                "1. North America",
                "2. South America",
                "3. Africa",
                "4. Asia",
                "5. Europe"
            ),
            "Question 2: Which country is known as the 'Land of the Rising Sun'?" => array(
                "1. Japan",
                "2. China",
                "3. South Korea",
                "4. Vietnam",
                "5. Thailand"
            ),
            "Question 3: What is the currency of Brazil?" => array(
                "1. Peso",
                "2. Euro",
                "3. Real",
                "4. Dollar",
                "5. Yen"
            ),
            "Question 4: Who was the first president of the United States?" => array(
                "1. Abraham Lincoln",
                "2. George Washington",
                "3. Thomas Jefferson",
                "4. John Adams",
                "5. Benjamin Franklin"
            ),
            "Question 5: What is the longest river in the world?" => array(
                "1. Amazon River",
                "2. Nile River",
                "3. Mississippi River",
                "4. Yangtze River",
                "5. Danube River"
            )
        ),
        "Science" => array(
            "Question 1: What is the chemical symbol for water?" => array(
                "1. O",
                "2. C",
                "3. H2O",
                "4. W",
                "5. A"
            ),
            "Question 2: What is the process by which plants make their food?" => array(
                "1. Photosynthesis",
                "2. Respiration",
                "3. Digestion",
                "4. Fermentation",
                "5. Transpiration"
            ),
            "Question 3: Which gas do plants absorb from the atmosphere?" => array(
                "1. Oxygen",
                "2. Nitrogen",
                "3. Carbon dioxide",
                "4. Hydrogen",
                "5. Helium"
            ),
            "Question 4: What is the largest organ in the human body?" => array(
                "1. Liver",
                "2. Lungs",
                "3. Brain",
                "4. Skin",
                "5. Heart"
            ),
            "Question 5: What is the force that pulls objects towards the center of the Earth?" => array(
                "1. Gravity",
                "2. Magnetism",
                "3. Friction",
                "4. Inertia",
                "5. Acceleration"
            )
        )
    );

    // Add the correct answers for each question
    $correct_answers = array(
        "English" => array(2, 1, 3, 2, 1),
        "SST" => array(4, 1, 3, 2, 1),
        "Science" => array(3, 1, 3, 4, 1)
    );

    return array($questions[$subject], $correct_answers[$subject]);
}

// Function to process the user input and return the USSD response
function processUSSD($text) {
    $textArray = explode('*', $text);
    $session_id = $textArray[0];
    $user_response = end($textArray);
    $menu_level = count($textArray);

    if ($menu_level <= 2) {
        // For levels 1 and 2, display a continued session (CON)
        $response = welcomeMessage();
        $ussd_response = "CON " . $response;
    } elseif ($menu_level == 3) {
        // Level 3, get subject selection and display quiz menu
        $subject = '';
        switch ($user_response) {
            case '1':
                $subject = 'English';
                break;
            case '2':
                $subject = 'SST';
                break;
            case '3':
                $subject = 'Science';
                break;
            default:
                // Invalid subject selection, display an error message
                $response = "CON Invalid selection. Please try again.\n";
                $response .= subjectsMenu();
                $ussd_response = "CON " . $response;
                return $ussd_response;
        }

        $response = quizMenu();
        // Store the selected subject and correct answers in the session variable
        $_SESSION['selected_subject'] = $subject;
        list($questions, $correct_answers) = getSubjectQuestions($subject);

        // Shuffle the answer options for each question
        $shuffled_questions = array_map(function ($question) {
            shuffle($question);
            return $question;
        }, $questions);

        // Store the shuffled questions, correct answers, and the current question index in the session variable
        $_SESSION['shuffled_questions'] = $shuffled_questions;
        $_SESSION['correct_answers'] = $correct_answers[$subject];
        $_SESSION['current_question'] = 0;
        $ussd_response = "CON " . $response;
    } elseif ($menu_level == 4) {
        // Level 4, handle the quiz menu selection (Start Quiz or Quit)
        if ($user_response == '0') {
            // User wants to quit, display a goodbye message
            $response = "Thank you for using the Quiz Platform. Goodbye!";
            $ussd_response = "END " . $response;
        } elseif ($user_response == '1') {
            // User wants to start a new quiz, display the subjects menu
            $response = subjectsMenu();
            $ussd_response = "CON " . $response;
        } elseif ($user_response == '2') {
            // User wants to start the quiz, display the first question
            $current_question = $_SESSION['current_question'];
            $question_text = getKeyByIndex($_SESSION['shuffled_questions'][$current_question]);
            $response = "$question_text\n" . implode("\n", $_SESSION['shuffled_questions'][$current_question]);
            $ussd_response = "CON " . $response;
        } else {
            // Invalid quiz menu selection, display an error message
            $response = "Invalid selection. Please try again.\n";
            $response .= quizMenu();
            $ussd_response = "CON " . $response;
        }
    } elseif ($menu_level > 4) {
        // Level 5 and above, handle quiz questions and answers
        $shuffled_questions = $_SESSION['shuffled_questions'];
        $correct_answers = $_SESSION['correct_answers'];
        $current_question = $_SESSION['current_question'];

        // Validate user response and get the next question or display results
        $selected_answer = intval($user_response);
        if ($selected_answer >= 1 && $selected_answer <= 5) {
            // Valid answer selection
            // You can handle scoring and results tracking here if needed
            $current_question++;
            if ($current_question < count($shuffled_questions)) {
                // There are more questions, display the next question
                $question_text = getKeyByIndex($shuffled_questions[$current_question]);
                $response = "$question_text\n" . implode("\n", $shuffled_questions[$current_question]);
                $ussd_response = "CON " . $response;
                $_SESSION['current_question'] = $current_question; // Update the current question index
            } else {
                // All questions completed, display the quiz results
                $score = calculateScore($shuffled_questions, $correct_answers, $textArray);
                $response = "Congratulations! You have completed the quiz.\nYour Score: $score / " . count($shuffled_questions) . "\n";
                $response .= "1. Start a new quiz\n";
                $response .= "0. Quit";
                $ussd_response = "CON " . $response;
            }
        } else {
            // Invalid answer selection, display an error message
            $question_text = getKeyByIndex($shuffled_questions[$current_question]);
            $response = "Invalid selection. Please try again.\n";
            $response .= "$question_text\n" . implode("\n", $shuffled_questions[$current_question]);
            $ussd_response = "CON " . $response;
        }
    }

    return $ussd_response;
}


// Function to get the question text from the array key
function getKeyByIndex($array) {
    reset($array);
    return key($array);
}

// Function to calculate the user's score
function calculateScore($shuffled_questions, $correct_answers, $user_responses) {
    $score = 0;
    $user_responses = array_slice($user_responses, 3); // Remove initial USSD inputs
    foreach ($user_responses as $index => $response) {
        $selected_answer = intval($response);
        $question_key = getKeyByIndex($shuffled_questions[$index]);
        $correct_answer_index = array_search($correct_answers[$_SESSION['selected_subject']][$question_key], $shuffled_questions[$index]);
        if ($selected_answer === ($correct_answer_index + 1)) {
            $score++;
        }
    }
    return $score;
}

// Main entry point for the USSD request
// Initialize session if it doesn't exist
if (!isset($_SESSION)) {
    session_start();
}

// Get the text from the USSD request (this may vary depending on your Africa's Talking API setup)
$text = $_GET['text'];

// Process the USSD request and get the response
$ussd_response = processUSSD($text);

// Print the response
header('Content-type: text/plain');
echo $ussd_response;
