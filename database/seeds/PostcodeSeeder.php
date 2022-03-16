<?php

use Illuminate\Database\Seeder;
use App\Models\Postcode;
use Illuminate\Support\Carbon;

class PostcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Create postcode data from postcode csv file.
         * Put postcode csv file manually from https://www.post.japanpost.jp/zipcode/dl/kogaki-zip.html
         */
        $data = file_get_contents('database/seeds/files/address_20210531.csv');
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS-win');
        $temp = tmpfile();
        $meta = stream_get_meta_data($temp);
        fwrite($temp, $data);
        rewind($temp);

        $file = new SplFileObject($meta['uri'], 'rb');

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        $index = 0;
        $carbon_now = Carbon::now();
        foreach ($file as $line) {
            $index++; 
            $list[] = [
                'id'              => $index,
                'postcode'        => $line[2],
                'prefecture_kana' => $line[3],
                'city_kana'       => $line[4],
                'local_kana'      => $line[5],
                'prefecture'      => $line[6],
                'city'            => $line[7],
                'local'           => $line[8],
                'created_at'      => $carbon_now,
                'updated_at'      => $carbon_now,
            ];
            if (count($list) > 1000) {
                Postcode::insert($list);
                $list = [];
            }
        }
        if (count($list)) {
            Postcode::insert($list);
        }
    }
}