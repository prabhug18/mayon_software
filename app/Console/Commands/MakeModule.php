<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Generate CRUD module (model, controller, request, resource, migration)';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $this->call('make:model', ['name' => $name, '--migration' => true]);
        $this->call('make:controller', ['name' => "{$name}Controller", '--resource' => true]);
        $this->call('make:request', ['name' => "{$name}Request"]);
        $this->call('make:resource', ['name' => "{$name}Resource"]);
        $this->info("CRUD module for {$name} generated successfully.");
    }
}
