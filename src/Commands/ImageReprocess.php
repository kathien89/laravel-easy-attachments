<?php

namespace BenAllfree\LaravelEasyAttachments\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;
use BenAllfree\LaravelEasyAttachments\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use BenAllfree\LaravelEasyAttachments\Jobs\ReprocessImageJob;

class ImageReprocess extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $signature = 'images:reprocess {--force} {--queue} {--preserve-files}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Reprocess all images.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $force = $this->option('force')!=null;
    $preserve = $this->option('preserve-files')!=null;
    $shouldQueue = $this->option('queue')!=null;
    
    $this->info(sprintf("Preserving files: %d", $preserve));
    $this->info(sprintf("Queuing mode: %d", $shouldQueue));
    $this->info(sprintf("Force mode: %d", $force));
    
    $save = config('easy-attachments.preserve_original_files');
    config(['easy-attachments.preserve_original_files'=>$preserve]);

    $Image = config('easy-attachments.image_class');
    $Image::query()->chunk(50, function($images) use ($force, $preserve, $shouldQueue) {
      foreach($images as $i)
      {
        
        if(!$force && !$i->should_reprocess()) continue;
        $this->info("Processing {$i->original_file_name}");
        try
        {
          if($shouldQueue)
          {
            $this->info("...queuing");
            ReprocessImageJob::dispatch($i, $force, $preserve);
          } else {
            $this->info("...reprocessing");
            $i->reprocess($force);
          }
        } catch (FileNotFoundException $e)
        {
          echo("\tFile not found.");
        }
      }
    });

    config(['easy-attachments.preserve_original_files'=>$save]);
  }
}
