<?php

namespace WalkerChiu\Firewall;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Firewall\Models\Entities\Setting;
use WalkerChiu\Firewall\Models\Entities\Item;
use WalkerChiu\Firewall\Models\Entities\ItemLang;

class ItemTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\Firewall\FirewallServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Item.
     *
     * For WalkerChiu\Firewall\Models\Entities\Item
     * 
     * @return void
     */
    public function testItem()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-firewall.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-firewall.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-firewall.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $db_setting = factory(Setting::class)->create();
        DB::table(config('wk-core.table.user'))->insert([
            [
                'id'       => 1,
                'name'     => $faker->username,
                'email'    => $faker->email,
                'password' => $faker->password
            ],[
                'id'       => 2,
                'name'     => $faker->username,
                'email'    => $faker->email,
                'password' => $faker->password
            ],[
                'id'       => 3,
                'name'     => $faker->username,
                'email'    => $faker->email,
                'password' => $faker->password
            ]
        ]);

        // Give
        $db_morph_1 = factory(Item::class)->create(['setting_id' => $db_setting->id, 'user_id' => 1]);
        $db_morph_2 = factory(Item::class)->create(['setting_id' => $db_setting->id, 'user_id' => 2]);
        $db_morph_3 = factory(Item::class)->create(['setting_id' => $db_setting->id, 'user_id' => 3]);

        // Get records after creation
            // When
            $records = Item::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_morph_2->delete();
            $records = Item::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Item::withTrashed()
                ->find($db_morph_2->id)
                ->restore();
            $record_2 = Item::find($db_morph_2->id);
            $records = Item::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);
    }
}
