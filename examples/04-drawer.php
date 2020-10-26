<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$sizes = json_decode(file_get_contents('data/psizes.json'), true);
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
$csvPath = 'data/file.csv';

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns (in any order)
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors]);
$parser->paramField('item_size', [$sizes])->required(true);
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
$result = $parser->parse();

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

// Change max text length for ['display' => 'text'] cells
$drawer->setTextLen(30);

// Disable text crop fro skipped columns
// $drawer->cropSkipped(false);


?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Drawer display</h3>
            <hr>
            For better displaying cells with links, images and text you can set appropriate display options.
            It can be done by passing 2nd argument to Drawer(). Also you can set column title like shown below:
            <pre><code>$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],                     // Set column title
    'item_desc' => ['title' => 'Description', 'display' => 'text'], // Crop text and set column title
    'item_link' => ['display' => 'link'],                           // Display cell as link
    'item_image1' => ['display' => 'image'],                        // Display cell as image
    'item_image2' => ['display' => 'image'],                        // Display cell as image
    'item_image3' => ['display' => 'image'],                        // Display cell as image
]);</code></pre>
            <p>
                <b>['title' => 'Product Name']</b> - Set column title<br>
                <b>['display' => 'text']</b> - cell text will be cropped (to 50 chars by default).
                Crop length can be set by <kbd>$drawer->setTextLen($len);</kbd><br>
                <b>['display' => 'link']</b> - "Link" will be displayed instead of cell value (url)<br>
                <b>['display' => 'image']</b> - Drawer will try to get image or file name and display it only<br>
            </p>
            <p>Note that all skipped columns ("image 4" in our case) is cropped by default. This behaviour can be
                disabled
                by <kbd>$drawer->cropSkipped(false);</kbd></p>
        </div>

        <div class="col-6">
            <h3>Parser results</h3>
            <?php dump($result) ?>
        </div>
    </div>
</div>


<h2>Drawer view</h2>
<table class="parse-table">
    <thead>
    <?php echo $drawer->head() ?>
    </thead>
    <tbody>
    <?php echo $drawer->body() ?>
    </tbody>
</table>


</body>
</html>