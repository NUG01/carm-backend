<?php

namespace App\Console\Commands;

use App\Enums\AuctionEnum\AuctionEnum;
use App\Models\Port;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import locations from a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $excel_paths = [
            [
                'path' => storage_path('app/excel/copart.csv'),
                'auction_id' => 1
            ],
            [
                'path' => storage_path('app/excel/iai.csv'),
                'auction_id' => 2
            ]
        ];


        foreach ($excel_paths as $excel) {
            $file = fopen($excel['path'], 'r');

            while (($line = fgetcsv($file)) !== false) {
                // Split the line into columns by the comma delimiter
                $name = explode(',', $line[0])[0];
                $price = explode(',', $line[1])[0];

                [
                    'city' => $city,
                    'state' => $state,
                    'port' => $port,
                ] = self::extractData($name);


                Port::create([
                    'label' => $name,
                    'price' => $price,
                    'city' => $city,
                    'state' => $state,
                    'port' => $port,
                    'auction_id' => $excel['auction_id'],
                ]);
            }
        }


        fclose($file);
        $this->info("Locations imported successfully.");
        return 0;
    }

    private static function extractData($string)
    {
        $pattern = '/^(.+?) – ([A-Z]{2}) \(პორტი (.+?)\)$/';

        if (preg_match($pattern, $string, $matches)) {
            return [
                'city' => $matches[1],
                'state' => $matches[2],
                'port' => $matches[3],
            ];
        }

        return null;
    }
}
