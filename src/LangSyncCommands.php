<?php

namespace Noughitarek\LaravelLangSync;

use Illuminate\Console\Command;

class LangSyncCommands extends Command
{
    protected $signature = 'lang:sync {locale?}';
    
    protected $description = 'sync lang files';

    public function handle()
    {
        $expressions = array();
        $locale = $this->argument('locale');
        if($locale==null)
        {
            $this->error('The locale is mandatory: enter the locale eg."FR"');
            return Command::FAILURE;
        }
        if(config('LangSync.dirs')!=null)
        {
            $dirs = config('LangSync.dirs');
        }
        else
        {
            $dirs[] =  resource_path('views');
            $dirs[] =  app_path();
        }
        foreach($dirs as $dir)
        {
            $files = $this->readFiles($dir);
            $expressions = array_merge($expressions, $this->getAll($files));
        }
        $this->makeLangFile($expressions, $locale);
    }
    private function readFiles($dir)
    {
        $files = array();
        if(is_dir($dir) && $readdir = opendir($dir))
        {
            while(($file = readdir($readdir)) !== false)
            {
                if($file=='.'||$file=='..')
                {
                    continue;
                }
                if(is_dir($dir.'\\'.$file))
                {
                    $files = array_merge($files, $this->readFiles($dir.'\\'.$file));
                }
                else
                {
                    $files[] = $dir.'\\'.$file;
                }
            }
        }
        return $files;
    }
    public function get($file)
    {
        $exp = '#(__|@lang)\([\'"]([^$]+)[\'"]\)#U';
        $fileContent = file_get_contents($file);
        preg_match_all($exp, $fileContent, $res);
        return $res[2];
    }
    public function getAll($files)
    {
        $expressions = array();
        if(is_array($files))
        {
            foreach($files as $file)
            {
                $expressions = array_merge($expressions, $this->get($file));
            }
        }
        return array_unique($expressions);
    }
    public function makeLangFile($expressions, $locale)
    {
        config(['app.locale'=>$locale]);
        $exps = array();
        foreach($expressions as $expression)
        {
            $exps[$expression] = __($expression) != ' '?__($expression):' ';
        }
        ksort($exps);
        file_put_contents(app_path().'/../lang/'.$locale.'.json', json_encode($exps, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));

        $this->line("Synced: ".count($exps).' lines added to '.$locale.'.json');
        return Command::SUCCESS;
    }
}