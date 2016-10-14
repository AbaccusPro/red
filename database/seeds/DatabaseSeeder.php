<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Config::get('app.env') == 'demo' || Config::get('app.env') == 'local') {
            $this->call(MediumTableSeeder::class);
            $this->call(CategoriesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(TimelinesTableSeeder::class);
            $this->call(SettingsTableSeeder::class);
            $this->call(HashtagsTableSeeder::class);
            $this->call(AnnouncementsTableSeeder::class);
            $this->call(PostsTableSeeder::class);
            $this->call(CommentsTableSeeder::class);
            $this->call(NotificationsTableSeeder::class);
            $this->call(StaticpageTableSeeder::class);
            // $this->call(InstallerSeeder::class);
        } else {
            // $this->call(MediumTableSeeder::class);
            $this->call(CategoriesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            // $this->call(TimelinesTableSeeder::class);
            // $this->call(SettingsTableSeeder::class);
            // $this->call(HashtagsTableSeeder::class);
            // $this->call(AnnouncementsTableSeeder::class);
            // $this->call(PostsTableSeeder::class);
            // $this->call(CommentsTableSeeder::class);
            // $this->call(NotificationsTableSeeder::class);
            $this->call(StaticpageTableSeeder::class);
            // $this->call(MessagesTableSeeder::class);
            $this->call(InstallerSeeder::class);
        }
    }
}
