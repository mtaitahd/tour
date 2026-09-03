<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "boot done\n"; flush();
set_time_limit(15);
try { $r = \Illuminate\Support\Facades\DB::select('select 1'); echo "raw select 1 OK\n"; } catch (\Throwable $e) { echo "raw EXC: ".$e->getMessage()."\n"; } flush();
try { $c = \App\Models\TourPackage::count(); echo "TourPackage::count = $c\n"; } catch (\Throwable $e) { echo "count EXC: ".$e->getMessage()."\n"; } flush();
try { $c = \App\Models\Destination::count(); echo "Destination::count = $c\n"; } catch (\Throwable $e) { echo "dest EXC: ".$e->getMessage()."\n"; } flush();
echo "END\n";
