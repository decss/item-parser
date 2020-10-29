<?php
require 'inc.head.php';
?>

<nav>
    <ul class="list-group">
        <li class="list-group-item"><h4>Context</h4></li>
        <li class="list-group-item"><a href="#features">Features</a></li>
        <li class="list-group-item"><a href="#installation">Installation</a></li>
        <li class="list-group-item">
            <a href="#parser-result">Parser result</a><br>
            &bull; <a class="small" href="#empty-field">Empty field</a><br>
            &bull; <a class="small" href="#text-field">Text field</a><br>
            &bull; <a class="small" href="#param-field">Param field and values</a><br>
        </li>
        <li class="list-group-item">
            <a href="#usage">Usage</a><br>
            &bull; <a class="small" href="#parser-usage">Parser usage</a><br>
            &bull; <a class="small" href="#drawer-usage">Drawer usage</a><br>
        </li>
        <li class="list-group-item">
            <a href="#detailed-usage">Detailed usage</a><br>
            &bull; <a class="small" href="#create-parser">Create Parser and set content</a><br>
            &bull; <a class="small" href="#configure-columns">Configure columns</a><br>
            &bull; <a class="small" href="#configure-parser">Configure parser options</a><br>
            &bull; <a class="small" href="#parsing-and-results">Parsing and results</a><br>
            &bull; <a class="small" href="#use-drawer">Use Drawer</a><br>
        </li>
    </ul>
</nav>

<div class="content">
<h3>ItemParser</h3>
<p>ItemParser is a simple PHP class for parsing Products and other records
with their parameters (like colors, sizes etc) from CSV, present results as array
or display it as html table </p>
<hr>


<h3 id="features">Features</h3>
<b>Parser</b> features:
<ul>
    <li>Parse data from csv to array</li>
    <li>Display parse results in table view</li>
    <li>Parse parameters like <i>size, color, material, category, etc</i> from cells like <i>"S; M; L; XL"</i>
        to array of [id => 1, value => "S"] items</li>
    <li>Detect missing parameters and give an ability to replace or ignore it</li>
    <li>Configure each column type and order or skip it</li>
    <li>Search parameters by value or aliases</li>
    <li>Skip specified rows or columns</li>
</ul>
<b>Drawer</b> features:
<ul>
    <li>Select, change or skip each column manually</li>
    <li>Display parameters as tags</li>
    <li>Mark tags as <i>ignored, replaced</i> or <i>not found</i></li>
    <li>Mark cell as <i>valid</i> or <i>invalid</i></li>
    <li>Shorten links and image urls</li>
    <li>Shorten long text</li>
    <li>Hide valid, invalid or custom rows</li>
</ul>
<hr>


<h3 id="installation">Installation</h3>
Using Composer run the following on the command line:
<pre><code>composer require decss/item-parser</code></pre>
Include Composer's autoloader file in your PHP script:
<pre><code>require_once __DIR__ . '/vendor/autoload.php';</code></pre>
<b>Without composer</b>
<p>
    Not recommended. To use ItemParser, you have to add a
    <kbd>require 'itemparser.lib.php';</kbd> line. <br>
    Note that you will need to require all ItemParser dependencies like <i>ParseCsv</i>.
</p>
<hr>


<h3 id="parser-result">Parser result</h3>
<div class="mb-3">
Paeser result is an array of rows (lines). Each row matches the corresponding line in the CSV and generally looks as follows:
<pre class="mb-0"><code>0 => [
    "row"    => 1,      // line number in CSV
    "valid"  => true,   // <i>true</i> if all row's Fields is valid, <i>false</i> if any is invalid
    "skip"   => false,  // <i>true</i> only if you skip this row by special method
    "fields" => []      // array of row fields (cells)
]</code></pre>
Skipped rows can be both valid ("valid" => true) or invalid ("valid" => false) and vice versa.
</div>
<p>
    As mentioned above, <i>"fields"</i> is an array of Field items. Each Field can be different depending on its type, config and content.<br>
    All row fields will be presented in result, even if Field was not parsed or was skipped or invalid - there is no matter.
</p>


<h5 id="empty-field">Empty field</h5>
<div class="mb-3">
This is an example of skipped or not configured Field:
<pre><code>14 => [
    "text" => "cell text",  // Original CSV cell text
    "name" => null,         // Field name from Parser Fields config
    "type" => null          // Field type
]</code></pre>
</div>


<h5 id="text-field">Text field</h5>
<div class="mb-3">
So there is 2 Field types: <i>text</i> and <i>param</i>. Here is example of configured <i>text</i> Field:
<pre class="mb-0"><code>1 => [
    "text" => "V_3J689910", //
    "name" => "item_sku",   //
    "type" => "text",       //
    "valid" => true,        // <i>true</i> if Field is not required or required and have valid "value"
    "value" => "V_3J689910" // Unlike <i>"text"</i>, <i>"value"</i> is the processed value of a cell.
]</code></pre>
<i>"value"</i> - is what you should to use instead of <i>"text"</i><br>
</div>


<h5 id="param-field">Param field and values</h5>
<div class="mb-3">
Next is <i>"param"</i> Field:
<pre class="mb-0"><code>3 => [
    "text" => "Black; Not a color; Grey; ",
    "name" => "item_color",
    "type" => "param",
    "valid" => false,
    "value" => [
        0 => [
            "valid" => true,    // <i>true</i> if param was found in Field params
            "skip" => false,    // <i>true</i> if this value was skipped in Field missings config
            "replace" => false, // <i>true</i> if this value was replaced in Field missings config
            "id" => 1,          // Param ID, if it's value was found by in Field params
            "value" => "Black", // Param or Replaced param value
            "text" => "Black"   // Param text extracted from cell text value
        ],
        1 => [
            "valid" => false,
            "skip" => false,
            "replace" => false,
            "id" => null,
            "value" => null,
            "text" => "Not a color"
        ],
        2 => ["valid" => true, "skip" => false, "replace" => false, "id" => 3, "value" => "Grey", "text" => "Grey"]
    ]
]</code></pre>
So you can see that <i>"value"</i> of <i>param</i> Field is an array. Here is example of both found [0,2] and not found [1] colors.<br>
If there is 2 or more identical colors (ie "Black; Red; Black") all fo them will be valid but duplicates will be skipped.
</div>
<hr>


<h3 id="usage">Usage</h3>
<h5 id="parser-usage">Parser usage</h5>
<pre><code>use ItemParser\Parser;

// 1. Init Parser and set CSV file path
$csvPath = 'file.csv';
$parser = new Parser($csvPath);

// 2. Config columns
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->textField('item_link');
$parser->textField('item_image1');
$parser->textField('item_image2');
// 2.1 Config param column
// Param array
$colors = [
    ['id' => 1, 'value' => 'Red'],
    ['id' => 2, 'value' => 'Green'],
    ['id' => 3, 'value' => 'Blue'],
    ['id' => 4, 'value' => 'Gold', 'alias' => ['Gold sand', 'Golden-Orange']],
];
// Param Missing - skip or replace colors, that was not found in $colors
$colorsMissing = [
    'Orange' => -1, //  Skip this color
    'Golden' => 4,  //  Replace "Golden" to "Gold" (id = 4)
];
$parser->paramField('item_color', [$colors, $colorsMissing])->required();

// 3. Run parse and get results
$result = $parser->parse();
</code></pre>
<br>

<h5 id="drawer-usage">Drawer usage</h5>
<pre><code>use ItemParser\Drawer;

// 1. Init Parser and set CSV file path
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['display' => 'image'],
]);

// Display results
echo '&lt;table class="parse-table"&gt;'
    . '&lt;thead&gt;' . $drawer->head() . '&lt;/thead&gt;'
    . '&lt;tbody&gt;' . $drawer->body() . '&lt;/tbody&gt;'
    . '&lt;/table&gt;';
</code></pre>
<hr>


<h3 id="detailed-usage">Detailed usage</h3>
<h5 id="create-parser">Create Parser and set content</h5>
<pre><code>// Set CSV file path
$parser = new Parser('file.csv');
// or
$parser = new Parser;
$parser->setCsvPath('file.csv');

// Set SCV content
$parser = new Parser;
$content = file_get_contents('file.csv');
$parser->setCsvContent($content);
</code></pre>
<p>You can access to <i>Csv()</i> instance of <i>ParseCsv</i> library and configure it directly:</p>
<pre>$csvObj = $parser->getCsvObj();
$csvObj->delimiter = ';,';  // Set CSV rows delimiter characters
</pre>
<br>

<h5 id="configure-columns">Configure columns</h5>
<pre><code>// Add text field
$parser->textField('column_name');
// Add required text field
$parser->textField('column2_name')->required();

// Add param field
$parser->paramField('item_size', [$sizes]);
// Add required param field with missing colors and set possible delimiters for params
$parser->paramField('item_color', [$colors, $colorsMissing])->required()->delimiters([';', ',', '/']);
</code></pre>
See examples to get how arguments like <i>$colors</i> and <i>$colorsMissing</i> work<br><br>

<h5 id="configure-parser">Configure parser options</h5>
<pre><code>// Skip first 2 rows
$parser->skipRows([0,1]);

// Skip columns and set order
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
</code></pre>
<br>

<h5 id="parsing-and-results">Parsing and results</h5>
<pre><code>// Do parsing and get results
$result = $parser->parse();

// Get results after parsing
$result = $parser->result();
</code></pre>
<br>

<h5 id="use-drawer">Use Drawer</h5>
<pre><code>// Create Drawer and config it
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_sku' => ['title' => 'Product SKU'],
    'item_price' => ['title' => 'Price'],
    'item_size' => ['title' => 'Sizes'],
    'item_color' => ['title' => 'Colors'],
    'item_desc' => ['title' => 'Description', 'display' => 'text'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['display' => 'image'],
    'item_image2' => ['display' => 'image'],
    'item_image3' => ['display' => 'image'],
]);

// Hide valid rows
$drawer->hideValid();
// Hide invalid rows
$drawer->hideInvalid();
// Hide custom rows
drawer->hideRows([0, 6, 7, 8]);

// Display missing selects
echo $drawer->missing();

// Display table column names
echo $drawer->head();
// Display table column names with Field selects
echo $drawer->head('select');

// Display table rows
echo $drawer->body();
</code></pre>


</div>

</body>
</html>