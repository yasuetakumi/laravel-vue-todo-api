<?php

use App\Models\DummyMeeting;
use Illuminate\Database\Seeder;

class DummyMeetingSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        factory(DummyMeeting::class, 40)->create();
    }
}
