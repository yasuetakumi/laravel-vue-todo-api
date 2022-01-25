<?php

namespace Tests\Browser;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    /**
     * Function for login.
     */
    public function executeLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.v-card')
                    ->type('input[type=text]', 'admin@company.com')
                    ->type('input[type=password]', '12345678')
                    ->press('button[type=submit]')
                    ->waitForText('ダッシュボード');
        });
    }

    /**
     * Function to test user create field required.
     */
    public function testUserCreateRequired()
    {
        $this->executeLogin();
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/create')
                    ->waitFor('.v-form')
                    ->press('button[type=submit]')
                    ->waitFor('.error--text')
                    ->assertSee('メールアドレス必須')
                    ->assertSee('名前必須')
                    ->assertSee('パスワード必須')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_CREATE_REQUIRED');
        });
    }

    /**
     * Function to test user create insert value to field.
     * Function to test user create submit data.
     */
    public function testUserInsert()
    {
        $user_count = User::count();
        $email = 'test'.$user_count.'@test.com';

        $this->browse(function (Browser $browser) use ($user_count, $email) {
            $browser->visit('/users/create')
                    ->waitFor('.v-form')
                    ->type('input[name=email]', $email)
                    ->type('input[name=full_name]', 'test name'.$user_count)
                    ->type('input[type=password]', '12345678')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_CREATE_INSERT')
                    ->press('button[type=submit]')
                    ->waitFor('.success')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_CREATE_SUBMIT');
        });

        $this->assertDatabaseHas('users', [
            'display_name'          => 'test name'.$user_count,
            'email'                 => $email,
        ]);
    }

    /**
     * Function to test user edit.
     * Function to test user edit submit data.
     */
    public function testUserEdit()
    {
        $user_count = User::count() - 1;
        $user_name = 'test name'.($user_count);
        $email = 'test_update'.$user_count.'@test.com';

        $this->browse(function (Browser $browser) use ($user_name, $user_count, $email) {
            $browser->visit('/users')
                    ->waitFor('.v-data-table')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_LIST')
                    ->with('table', function ($table) use ($user_name) {
                        $table->waitForText($user_name)
                        ->assertSee($user_name)
                        ->press('#edit');
                    })
                    ->waitFor('.v-form')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_EDIT')
                    ->keys('input[name=email]', ...array_fill(0, strlen($browser->value('input[name=email]')), '{backspace}'))
                    ->keys('input[name=full_name]', ...array_fill(0, strlen($browser->value('input[name=full_name]')), '{backspace}'))
                    ->type('input[name=email]', $email)
                    ->type('input[name=full_name]', 'test name update'.$user_count)
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_EDIT_INSERT')
                    ->press('button[type=submit]')
                    ->waitFor('.success')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_EDIT_SUBMIT');
        });

        $this->assertDatabaseHas('users', [
            'display_name'          => 'test name update'.$user_count,
            'email'                 => $email,
        ]);
    }

    /**
     * Function to test user delete.
     */
    public function testUserDelete()
    {
        $user_count = User::count() - 1;
        $user_name = 'test name update'.($user_count);
        $email = 'test_update'.$user_count.'@test.com';

        $this->browse(function (Browser $browser) use ($user_name, $user_count, $email) {
            $browser->visit('/users')
                    ->waitFor('.v-data-table')
                    ->with('table', function ($table) use ($user_name) {
                        $table->waitForText($user_name)
                        ->assertSee($user_name)
                        ->press('#delete');
                    })
                    ->waitFor('.v-dialog--active')
                    ->pause(3000)
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_DELETE_CONFIRMATION')
                    ->press('.success--text')
                    ->waitFor('.success')
                    ->screenshot(Carbon::now()->format('Y-m-d') . '_USER_DELETE_SUBMIT');
        });
    }
}
