<?php
/**
 * ==========================================================
 *  CAESAR CIPHER ENCRYPTION AND DECRYPTION SYSTEM
 *  Laboratory Activity - Web-Based PHP Application
 * ==========================================================
 *  Features:
 *   - Encrypt / Decrypt text using the Caesar Cipher algorithm
 *   - Preserves letter case, leaves non-alphabetic characters unchanged
 *   - Step-by-step character transformation table (with ASCII values)
 *   - Character frequency analysis (bonus feature)
 *   - Clear/Reset functionality
 * ==========================================================
 */

// ---------- Default values ----------
$originalText   = '';
$key            = 3;
$operation      = '';
$resultText     = '';
$processSteps   = [];   // holds each character's transformation data
$frequency      = [];   // bonus: character frequency analysis

/**
 * Shifts a single alphabetic character by $shift positions,
 * wrapping around the alphabet (A-Z or a-z). Non-alphabetic
 * characters are returned unchanged.
 */
function shiftChar(string $char, int $shift): string {
    $ascii = ord($char);

    // Uppercase A-Z (65-90)
    if ($ascii >= 65 && $ascii <= 90) {
        $newAscii = (($ascii - 65 + $shift) % 26 + 26) % 26 + 65;
        return chr($newAscii);
    }

    // Lowercase a-z (97-122)
    if ($ascii >= 97 && $ascii <= 122) {
        $newAscii = (($ascii - 97 + $shift) % 26 + 26) % 26 + 97;
        return chr($newAscii);
    }

    // Non-alphabetic characters (spaces, numbers, punctuation) stay the same
    return $char;
}

/**
 * Runs the Caesar Cipher over the whole input string and
 * builds the step-by-step process log used for the process table.
 */
function caesarCipher(string $text, int $key, string $operation): array {
    $shift = ($operation === 'encrypt') ? $key : -$key;
    $result = '';
    $steps = [];

    $length = strlen($text);
    for ($i = 0; $i < $length; $i++) {
        $char = $text[$i];
        $transformed = shiftChar($char, $shift);
        $result .= $transformed;

        // Only log alphabetic characters in the process table
        if (ctype_alpha($char)) {
            $steps[] = [
                'char'      => $char,
                'ascii'     => ord($char),
                'key'       => ($operation === 'encrypt') ? "+$key" : "-$key",
                'operation' => ($operation === 'encrypt') ? 'Encrypt' : 'Decrypt',
                'result'    => $transformed,
                'result_ascii' => ord($transformed),
            ];
        }
    }

    return ['result' => $result, 'steps' => $steps];
}

/**
 * Bonus: simple character frequency analysis (letters only, case-insensitive).
 */
function characterFrequency(string $text): array {
    $text = strtoupper($text);
    $freq = [];
    for ($i = 0; $i < strlen($text); $i++) {
        $c = $text[$i];
        if (ctype_alpha($c)) {
            $freq[$c] = ($freq[$c] ?? 0) + 1;
        }
    }
    ksort($freq);
    return $freq;
}

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] !== 'clear') {

    $originalText = $_POST['text'] ?? '';
    $key          = isset($_POST['key']) ? (int)$_POST['key'] : 0;
    $operation    = $_POST['action'] === 'encrypt' ? 'encrypt' : 'decrypt';

    // Normalize key so it always falls within 0-25 for consistent wrap-around
    $key = $key % 26;

    if ($originalText !== '') {
        $output       = caesarCipher($originalText, $key, $operation);
        $resultText   = $output['result'];
        $processSteps = $output['steps'];
        $frequency    = characterFrequency($originalText);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Caesar Cipher Encryption System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <header class="app-header">
        <h1>🔐 Caesar Cipher Encryption &amp; Decryption System</h1>
        <p class="subtitle">Classical Cryptography Demonstration — PHP Web Application</p>
    </header>

    <main>
        <!-- ============= INPUT PANEL ============= -->
        <section class="panel input-panel">
            <h2>Input</h2>
            <form method="POST" action="" id="cipherForm">
                <label for="text">Enter Text (words, sentences, or paragraphs)</label>
                <textarea id="text" name="text" rows="6" placeholder="Type or paste your message here..." required><?= htmlspecialchars($originalText) ?></textarea>

                <div class="controls-row">
                    <div class="control-group">
                        <label for="key">Encryption Key (Shift Value)</label>
                        <input type="number" id="key" name="key" value="<?= htmlspecialchars((string)$key) ?>" min="0" max="25" required>
                    </div>
                    <div class="control-group">
                        <button type="button" id="randomKeyBtn" class="btn btn-ghost">🎲 Random Key</button>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" name="action" value="encrypt" class="btn btn-primary">Encrypt</button>
                    <button type="submit" name="action" value="decrypt" class="btn btn-secondary">Decrypt</button>
                    <button type="submit" name="action" value="clear" formnovalidate class="btn btn-clear">Clear / Reset</button>
                </div>
            </form>
        </section>

        <?php if ($resultText !== ''): ?>
        <!-- ============= OUTPUT SUMMARY ============= -->
        <section class="panel output-panel">
            <h2>Output</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Original Text</span>
                    <span class="summary-value"><?= htmlspecialchars($originalText) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Encryption Key</span>
                    <span class="summary-value"><?= htmlspecialchars((string)$key) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Selected Operation</span>
                    <span class="summary-value operation-badge <?= $operation ?>"><?= ucfirst($operation) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label"><?= $operation === 'encrypt' ? 'Encrypted Text' : 'Decrypted (Original) Text' ?></span>
                    <span class="summary-value result-text" id="resultText"><?= htmlspecialchars($resultText) ?></span>
                </div>
            </div>
            <button type="button" id="copyBtn" class="btn btn-ghost">📋 Copy Result</button>
        </section>

        <!-- ============= PROCESS TABLE ============= -->
        <section class="panel process-panel">
            <h2>Step-by-Step Character Transformation</h2>
            <div class="table-wrapper">
                <table class="process-table">
                    <thead>
                        <tr>
                            <th>Character</th>
                            <th>ASCII</th>
                            <th>Key</th>
                            <th>Operation</th>
                            <th>Result</th>
                            <th>Result ASCII</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processSteps as $step): ?>
                        <tr>
                            <td class="char-cell"><?= htmlspecialchars($step['char']) ?></td>
                            <td><?= $step['ascii'] ?></td>
                            <td><?= $step['key'] ?></td>
                            <td><span class="op-tag <?= strtolower($step['operation']) ?>"><?= $step['operation'] ?></span></td>
                            <td class="result-cell"><?= htmlspecialchars($step['result']) ?></td>
                            <td><?= $step['result_ascii'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ============= BONUS: FREQUENCY ANALYSIS ============= -->
        <section class="panel frequency-panel">
            <h2>Character Frequency Analysis <span class="bonus-tag">Bonus</span></h2>
            <div class="frequency-grid">
                <?php
                $maxFreq = !empty($frequency) ? max($frequency) : 1;
                foreach ($frequency as $letter => $count):
                    $barHeight = max(6, intdiv($count * 100, $maxFreq));
                ?>
                <div class="freq-bar-wrapper">
                    <div class="freq-bar" style="height: <?= $barHeight ?>%;" title="<?= $letter ?>: <?= $count ?>"></div>
                    <span class="freq-letter"><?= $letter ?></span>
                    <span class="freq-count"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer class="app-footer">
        <p>Laboratory Activity — Caesar Cipher Encryption and Decryption Using PHP</p>
    </footer>
</div>

<script src="script.js"></script>
</body>
</html>
