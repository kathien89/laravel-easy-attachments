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

    public function __construct(Image $image, $force=false)
    {
      $this->image = $image;
      $this->force = $force;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->image->reprocess($this->force);
    }
}
