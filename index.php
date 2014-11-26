<html>
    <head>
        <title>Recurring Library</title>
    </head>
    <body>
        <h2>Recurring Library</h2>
        <?php if (!isset($_POST['action']) == 'submitted') { ?>
            <form name="recurring" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                Date scheduled:<input name="dat_start" id="dat_start" type="date" value="<?= date("Y-m-d", mktime(0, 0, 0, 1, 6, 2014)) ?>">
                <br />
                <hr />
                Recurring type<br />
                <input type="radio" name="settings" value="daily"> Daily<br>
                <input type="radio" name="settings" value="weekly" checked="true"> Weekly<br>
                <input type="radio" name="settings" value="m-w-f"> Monday-Wednesday-Friday<br />
                <input type="radio" name="settings" value="custom"> You can choose:
                <input type="checkbox" name="days[]" value="Monday">Monday
                <input type="checkbox" name="days[]" value="Tuesday">Tuesday
                <input type="checkbox" name="days[]" value="Wednesday">Wednesday
                <input type="checkbox" name="days[]" value="Thursday">Thursday
                <input type="checkbox" name="days[]" value="Friday">Friday
                <input type="checkbox" name="days[]" value="Saturday">Saturday
                <input type="checkbox" name="days[]" value="Sunday">Sunday
                <hr />
                Repetition<br />
                <input type="radio" name="repeat" value="until">till:<input name="dat_until" id="dat_until" type="date" value="<?= date("Y-m-d", mktime()) ?>"><br />
                <input type="radio" name="repeat" value="nor" checked="true">number of repetitions:<input name="num_rep" id="num_rep" type="number"><br />
                <input type="radio" name="repeat" value="nolimit">forever<br />
                <hr />
                Date range to display<br />
                From: <input name="dat_range1" id="dat_range1" type="date" value="<?= date("Y-m-d", mktime(0, 0, 0, 3, 1, 2014)) ?>"> To:<input name="dat_range2" id="dat_range2" type="date" value="<?= date("Y-m-d", mktime(0, 0, 0, 3, 31, 2014)) ?>"><br />
                <hr />
                <input type="hidden" name="action" value="submitted" />
                <input class="submit-button" type="submit" name="Submit" id="Submit" value="Submit" />
            </form>


            <?php
        } else {
            //form processing
            //display all error except notice
            error_reporting(E_ALL ^ E_NOTICE);
            
            //get form data
            $start = $_POST['dat_start'];
            $settings = $_POST['settings'];
            $repetition = $_POST['repeat'];
            $dat_range1 = $_POST['dat_range1'];
            $dat_range2 = $_POST['dat_range2'];
            
            //function for data dispaly
            function displayData($workDate, $show_from, $show_to) {
                if (($workDate->format("Y-m-d") >= $show_from->format('Y-m-d')) and ( $workDate->format("Y-m-d") <= $show_to->format('Y-m-d'))) {
                    echo $workDate->format("Y-m-d");
                    echo '<br>';
                }
            }

            //check date variables is empty
            if (empty($start)) {
                echo 'Date scheduled is not set.';
                exit;
            }
            if (empty($dat_range1)) {
                echo 'Date "From" in range display is not set.';
                exit;
            }
            if (empty($dat_range2)) {
                echo 'Date "To" in range display is not set.';
                exit;
            }

            //created date variables
            $publishDate = DateTime::createFromFormat("Y-m-d", $start);
            $show_from = DateTime::createFromFormat("Y-m-d", $dat_range1);
            $show_to = DateTime::createFromFormat("Y-m-d", $dat_range2);

            //settings array for days (other is period)
            $settings_opt = array("m-w-f", "custom");

            //display conditions
            echo '<b>Conditions:</b><br>';
            echo 'Start date: ' . $publishDate->format("Y-m-d");
            echo '<br>';
            echo 'Settings: ' . $settings;
            echo '<br>';
            echo 'Repetition: ' . $repetition;
            echo '<br>';
            echo 'Display date from: ' . $show_from->format("Y-m-d") . ", to: " . $show_to->format("Y-m-d");
            echo '<br>';
            echo '<hr>';

            //display results
            echo '<b>Results:</b><br>';
            //get settings and create $interval for period or days
            switch ($settings) {
                case "daily":
                    $interval = new DateInterval("P1D");
                    break;
                case "weekly":
                    $interval = new DateInterval("P1W");
                    break;
                case "m-w-f":
                    $interval = array("Monday", "Wednesday", "Friday");
                    break;
                case "custom":
                    if (!is_array($interval = $_POST['days'])) {
                        echo 'You must select a day(s).';
                        exit;
                    }
                    break;
            }

            //main operations and display data
            switch ($repetition) {
                case "until":
                    $until_date = $_POST['dat_until'];
                    if (empty($until_date)) {
                        echo 'Till date not set.';
                        exit;
                    }
                    $stop_date = DateTime::createFromFormat("Y-m-d", $until_date);
                    if (!in_array($settings, $settings_opt)) {
                        $workDate = $publishDate->add($interval);
                        while ($workDate->format("Y-m-d") <= $stop_date->format("Y-m-d")) {
                            displayData($workDate, $show_from, $show_to);
                            $workDate = $workDate->add($interval);
                        }
                    } else {
                        $workDate = $publishDate;
                        while ($workDate->format("Y-m-d") <= $stop_date->format("Y-m-d")) {
                            if (in_array($workDate->format("l"), $interval)) {
                                displayData($workDate, $show_from, $show_to);
                            }
                            $workDate = $workDate->modify('+1 day');
                        }
                    }
                    break;
                case "nor":
                    $num_rep = $_POST['num_rep'];
                    if (!in_array($settings, $settings_opt)) {
                        $publishDate = $publishDate->add($interval);
                        $period = new DatePeriod($publishDate, $interval, $num_rep - 1);
                        foreach ($period as $workDate) {
                            displayData($workDate, $show_from, $show_to);
                        }
                    } else {
                        $workDate = $publishDate->modify("+1 day");
                        $i = 1;
                        while ($i <= $num_rep) {
                            if (in_array($workDate->format("l"), $interval)) {
                                displayData($workDate, $show_from, $show_to);
                                $i++;
                            }
                            $workDate = $workDate->modify('+1 day');
                        }
                    }
                    break;
                case "nolimit":
                    if (!in_array($settings, $settings_opt)) {
                        $workDate = $publishDate->add($interval);
                        while ($workDate->format("Y-m-d") <= $show_to->format("Y-m-d")) {
                            displayData($workDate, $show_from, $show_to);
                            $workDate = $workDate->add($interval);
                        }
                    } else {
                        $workDate = $publishDate->modify("+1 day");
                        while ($workDate->format("Y-m-d") <= $show_to->format("Y-m-d")) {
                            if (in_array($workDate->format("l"), $interval)) {
                                displayData($workDate, $show_from, $show_to);
                            }
                            $workDate = $workDate->modify('+1 day');
                        }
                    }
                    break;
            }
        }
        ?>
    </body>
</html>
