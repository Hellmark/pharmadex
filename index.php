<?php
session_start();

// Load data from JSON file
function loadData() {
    $jsonFile = 'quiz_data.json';
    if (!file_exists($jsonFile)) {
        die("Error: quiz_data.json not found. Please create it with your Pokémon and pharmaceutical names.");
    }
    $json = file_get_contents($jsonFile);
    return json_decode($json, true);
}

// Initialize quiz
if (!isset($_SESSION['quiz_started']) && !isset($_POST['start_quiz'])) {
    // Show start screen
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pokémon or Pharmaceutical?</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 20px;
            }
            .intro {
                text-align: center;
                margin-bottom: 30px;
                color: #666;
                line-height: 1.6;
            }
            button {
                display: block;
                width: 100%;
                padding: 15px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 18px;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #5568d3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Pokémon or Pharmaceutical?</h1>
            <div class="intro">
                <p>Can you tell the difference between a Pokémon and a pharmaceutical drug?</p>
                <p>You'll see 20 random names. Guess which category each belongs to!</p>
            </div>
            <form method="post">
                <button type="submit" name="start_quiz">Start Quiz</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Start new quiz
if (isset($_POST['start_quiz'])) {
    $allData = loadData();
    shuffle($allData);
    $_SESSION['quiz_data'] = array_slice($allData, 0, 20);
    $_SESSION['quiz_started'] = true;
    $_SESSION['answers'] = [];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Process quiz submission
if (isset($_POST['submit_quiz'])) {
    $_SESSION['answers'] = $_POST['answers'] ?? [];
    $_SESSION['quiz_completed'] = true;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Show results
if (isset($_SESSION['quiz_completed']) && $_SESSION['quiz_completed']) {
    $quizData = $_SESSION['quiz_data'];
    $userAnswers = $_SESSION['answers'];
    $correct = 0;
    $results = [];

    foreach ($quizData as $index => $item) {
        $userAnswer = $userAnswers[$index] ?? null;
        $isCorrect = ($userAnswer === $item['type']);
        if ($isCorrect) $correct++;
        $results[] = [
            'name' => $item['name'],
            'type' => $item['type'],
            'user_answer' => $userAnswer,
            'correct' => $isCorrect
        ];
    }

    $percentage = round(($correct / 20) * 100);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quiz Results</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 900px;
                margin: 30px auto;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 10px;
            }
            .score {
                text-align: center;
                font-size: 48px;
                font-weight: bold;
                color: #667eea;
                margin: 20px 0;
            }
            .result-item {
                padding: 15px;
                margin: 10px 0;
                border-radius: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .correct {
                background: #d4edda;
                border-left: 4px solid #28a745;
            }
            .incorrect {
                background: #f8d7da;
                border-left: 4px solid #dc3545;
            }
            .name {
                font-weight: bold;
                font-size: 18px;
            }
            .type {
                font-size: 14px;
                color: #666;
            }
            button {
                display: block;
                width: 100%;
                padding: 15px;
                margin-top: 30px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 18px;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #5568d3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Quiz Results</h1>
            <div class="score"><?php echo $percentage; ?>%</div>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">
                You got <?php echo $correct; ?> out of 20 correct!
            </p>

            <?php foreach ($results as $result): ?>
                <div class="result-item <?php echo $result['correct'] ? 'correct' : 'incorrect'; ?>">
                    <div>
                        <div class="name"><?php echo htmlspecialchars($result['name']); ?></div>
                        <div class="type">
                            Actually: <?php echo ucfirst($result['type']); ?>
                            <?php if (!$result['correct']): ?>
                                | You guessed: <?php echo $result['user_answer'] ? ucfirst($result['user_answer']) : 'No answer'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="font-size: 24px;">
                        <?php echo $result['correct'] ? '✓' : '✗'; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <button type="submit" name="restart">Try Again</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    session_destroy();
    exit;
}

// Show quiz
if (isset($_SESSION['quiz_started'])) {
    $quizData = $_SESSION['quiz_data'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Take the Quiz</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 800px;
                margin: 30px auto;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }
            h1 {
                color: #333;
                text-align: center;
                margin-bottom: 30px;
            }
            .question {
                padding: 20px;
                margin: 15px 0;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #667eea;
            }
            .question-name {
                font-size: 24px;
                font-weight: bold;
                color: #333;
                margin-bottom: 15px;
            }
            .options {
                display: flex;
                gap: 15px;
            }
            .option {
                flex: 1;
            }
            .option input[type="radio"] {
                display: none;
            }
            .option label {
                display: block;
                padding: 12px;
                background: white;
                border: 2px solid #ddd;
                border-radius: 6px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s;
            }
            .option input[type="radio"]:checked + label {
                background: #667eea;
                color: white;
                border-color: #667eea;
            }
            .option label:hover {
                border-color: #667eea;
            }
            button {
                display: block;
                width: 100%;
                padding: 15px;
                margin-top: 30px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 18px;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #218838;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Pokémon or Pharmaceutical?</h1>
            <form method="post">
                <?php foreach ($quizData as $index => $item): ?>
                    <div class="question">
                        <div class="question-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="options">
                            <div class="option">
                                <input type="radio" id="pokemon_<?php echo $index; ?>"
                                       name="answers[<?php echo $index; ?>]" value="pokemon">
                                <label for="pokemon_<?php echo $index; ?>">Pokémon</label>
                            </div>
                            <div class="option">
                                <input type="radio" id="drug_<?php echo $index; ?>"
                                       name="answers[<?php echo $index; ?>]" value="drug">
                                <label for="drug_<?php echo $index; ?>">drug</label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_quiz">Submit Quiz</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
