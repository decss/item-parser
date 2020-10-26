<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <style>
        /* Example pages style */
        body, table {margin: 4px;}
        table {font-size: 13px;}
        table td {padding: 2px 3px; border: 1px solid #666;}
        /*pre {margin-left: 20px;}*/
        select {font-size: 11px;}
        .missing-tables table {display: inline-block; vertical-align: top;}
        nav {
            position: fixed;
            right: 20px;
        }
        nav ul {width: 240px;}
        .content {
            margin-right: 280px;
        }


        /* Drawer table style */
        .parse-table thead td {font-weight: bold;}
        .parse-table td.hidden {text-align: center; font-style: italic; padding: 6px 3px;}

        .parse-table .tag {
            vertical-align: top;
            font-size: 0.9em;
            display: inline-block;
            border: 1px solid gray;
            border-radius: 5px;
            padding: 1px 4px;
            margin: 0 4px 4px 0;
            cursor: pointer;
            min-height: 1.2em;
            min-width: 1.2em;
        }

        .parse-table .tag {background-color: #fff; color: #000;}
        .parse-table .tag.invalid {background-color: #ff2014;}
        .parse-table .tag.replaced {background-color: #14ff3b;}
        .parse-table .tag.skipped {background-color: #ccc; color:#666;}

        .parse-table tr.skipped {background-color: #ccc; color:#666;}
        .parse-table td.invalid {background-color: #ff2014;}
        .parse-table td.skipped {background-color: #ccc; color:#666;}
        .parse-table tr.invalid > td:first-child {background-color: #ff2014;}
    </style>
</head>
<boddy>



<ul class="nav nav-tabs mt-2 mb-4">
    <li class="nav-item"><a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Examples:</a></li>
    <?php
    $pages = [
        'index.php' => 'Index',
        '01-full.php' => '01-All features',
        '02-basic.php' => '02-Basic',
        '03-rows-columns.php' => '03-Rows/Columns',
        '04-drawer.php' => '04-Drawer display',
        '05-select-columns.php' => '05-Select columns',
        '06-params.php' => '06-Missing params',
        '07-aliases.php' => '07-Param aliases',
        '08-performance.php' => '08-Performance',
    ];
    foreach ($pages as $page => $name) {
        echo '<li class="nav-item"><a class="nav-link ' . (stristr($_SERVER['PHP_SELF'], $page) ? 'active' : '') . '" href="' . $page . '">' . $name . '</a></li>';
    }
    ?>
</ul>
<?php
if (!function_exists('dump')) {
    function dump($array) {
        echo '<p>For better output run: <kbd>composer require --dev symfony/var-dumper:^3.4</kbd></p>';
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}
?>
