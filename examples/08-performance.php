<?php
require __DIR__ . '/../vendor/autoload.php';
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Helpers;
use ItemParser\Parser;


// Generate ~1000 Sizes
$sizes = json_decode(file_get_contents('data/psizes.json'), true);
for ($i = 0; $i <= 980; $i++) {
    $sizes[] = [
        'id' => ($i + 20),
        'value' => 'Size ' . $i,
    ];
}
shuffle($sizes);

// Generate ~1000 Colors
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
for ($i = 0; $i <= 990; $i++) {
    $colors[] = [
        'id' => ($i + 20),
        'value' => 'Color ' . $i,
    ];
}
shuffle($colors);

// Generate ~1000 CSV rows
$csvPath = 'data/file.csv';
$content = file_get_contents($csvPath);
$tmp = $content;
$tmp = trim(substr_replace($tmp, null, 0, strpos($tmp, "\n")));
for ($i = 0; $i <= 98; $i++) {
    $content .= $tmp . "\r\n";
}


$tStart = microtime(true);
$mStart = memory_get_usage();


// 1. Init Parser and set CSV file path
$t = microtime(true);
$parser = new Parser();
$parser->setCsvContent($content);
$time['Load CSV'] = microtime(true) - $t;


// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns (in any order)
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors, $_POST['parseMissing']['item_color']]);
$parser->paramField('item_size', [$sizes, $_POST['parseMissing']['item_size']])->required(true);
$parser->textField('item_size_text');
$parser->textField('item_collection');
$parser->textField('item_material');
$parser->textField('item_desc')->required();
$parser->textField('item_link');
$parser->textField('item_image1');
$parser->textField('item_image2');
$parser->textField('item_image3');

// 2.3. Set columns ordering and skip some columns
$parser->fieldsOrder([
    0 => 'item_name',
    1 => 'item_sku',
    2 => 'item_price',
    3 => 'item_color',
    4 => 'item_size',
    // 5, 6 - skip
    7 => 'item_material',
    8 => 'item_desc',
    9 => 'item_link',
    10 => 'item_image1',
    11 => 'item_image2',
    12 => 'item_image3',
    // further will be skipped
]);

// 3. Run parse and get results
$t = microtime(true);
$result = $parser->parse();
$time['Parsing'] = microtime(true) - $t;

// 4. Init Drawer with options
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_sku' => ['title' => 'Prod. SKU'],
    'item_price' => ['title' => 'Price'],
    'item_size' => ['title' => 'Sizes'],
    'item_color' => ['title' => 'Colors'],
    'item_desc' => ['title' => 'Description', 'display' => 'text'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['title' => 'First Image', 'display' => 'image'],
    'item_image2' => ['title' => 'Second Image', 'display' => 'image'],
    'item_image3' => ['title' => 'Third Image', 'display' => 'image'],
]);


// Drawer render emulate
$t = microtime(true);
$drawer->missing();
$drawer->head();
$drawer->body();
$time['Drawing'] = microtime(true) - $t;

// Stats
$mEnd = memory_get_usage();

// Round time
$time['Total'] = microtime(true) - $tStart;
foreach ($time as $key => $val) {
    $time[$key] = round($val, 3);
}

$stats = [
    'Sizes count' => count($sizes),
    'Colors count' => count($colors),
    'CSV Rows' => $parser->rows(),
    'Memory' => number_format(($mEnd - $mStart) / 1024, 2, '.', ' ') . ' Kb',
];


// After measuring memory AND  drawer time we can hide rows
$drawer->hideRows(range(15, $parser->rows() - 10));
?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Performance</h3>
            <hr>

            <p>
                Here is an example of parsing CSV with 1000 rows with 2 Param columns, configured with 1000 param values for each
            </p>
            <p>
                First 10 lines has 15 colors and 34 sizes. All other lines are the copy of first 10. Whole 1000 lines contains about 1500 colors and 3400 sizes.<br>
                Assuming that each of the <b>$sizes</b> and <b>$colors</b> arrays has 1000 items, can be concluded
                that there was 1500 * 1000 + 3400 * 1000 value comparisons which gives us theoretically up to <b>5 000 000</b> comparisons (and this does not include aliases)<br>
            </p>
            <p>
                But the real number is much less due to caching and the search stops when the parameter is found.<br>
                You can se how much time it takes, take a look at <b>"Parsing"</b> time (<?php echo $time['Parsing']; ?> sec)
            </p>
        </div>

        <div class="col-6">
            <h3>Statistics</h3>

            <b>Usage:</b>
            <?php dump($stats); ?>

            <b>Time (sec):</b>
            <?php dump($time); ?>
        </div>
    </div>
</div>


<h2>Drawer view</h2>
<form action="" method="POST">
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>
    Missing params and display options:
    <div class="missing-tables">
        <?php echo $drawer->missing() ?>
    </div>

    <table class="parse-table">
        <thead>
        <?php echo $drawer->head() ?>
        </thead>
        <tbody>
        <?php echo $drawer->body() ?>
        </tbody>
    </table>
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>
</form>


</body>
</html>