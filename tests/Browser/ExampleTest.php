<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    // public function testBasicExample()
    // {
    //     $this->browse(function (Browser $browser) {
    //         $browser->visit('/login')
    //                 ->waitFor('.v-card')
    //                 ->type('input[type=text]', 'admin@company.com')
    //                 ->type('input[type=password]', '12345678')
    //                 ->press('button[type=submit]')
    //                 // ->assertPathIs('/login')
    //                 // ->assertSee('SPA');
    //                 ->waitForText('ダッシュボード')
    //                 ->visit('/users')
    //                 ->waitFor('.v-data-table')
    //                 ->waitFortext('Administrator')
    //                 ->assertSee('Administrator');
    //     });
    // }
}
