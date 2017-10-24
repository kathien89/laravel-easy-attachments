<?php

namespace BenAllfree\LaravelEasyAttachments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use BenAllfree\LaravelEasyAttachments\Image;

class ReprocessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;
    public $tries = 5;
    public $image;
    public $force;
    public $preserveFiles;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Image $image, $force=false, $preserveFiles=false)
    {
      $this->image = $image;
      $this->force = $force;
      $this->preserveFiles = $preserveFiles;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $save = config('easy-attachments.preserve_original_files');
      config(['easy-attachments.preserve_original_files'=>$this->preserveFiles]);
      $this->image->reprocess($this->force);
      config(['easy-attachments.preserve_original_files'=>$save]);
    }
}
