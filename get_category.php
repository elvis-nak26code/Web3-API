<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$category = App\Models\Category::where('company_id', 1)->first();
if ($category) {
    echo $category->id;
} else {
    echo "No category found";
}
