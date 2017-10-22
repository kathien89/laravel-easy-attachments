<?php

namespace BenAllfree\LaravelEasyAttachments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use BenAllfree\LaravelEasyAttachments\Image;

class FetchImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;
    public $tries = 5;
    public $image;

    public function __construct(Image $image)
    {
      $this->image = $image;
    }

    public function handle()
    {
      $this->image->att = $this->image->original_file_name;
      $this->image->save();
    }
}
