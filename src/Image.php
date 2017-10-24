<?php
namespace BenAllfree\LaravelEasyAttachments;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;
use BenAllfree\LaravelEasyAttachments\Jobs\ProcessImageJob;

class Image  extends \Eloquent implements StaplerableInterface 
{
  static protected $mode = 'move';

  use EloquentTrait {
    boot as attachmentBoot;
  }
  
  protected $fillable = ['original_file_name'];

  public static function createImageFromUrl($url)
  {
    $Image = config('easy-attachments.image_class');
    $i = $Image::whereOriginalFileName($url)->first();
    if(!$i)
    {
      $i = new $Image();
      $i->original_file_name = $url;
      $i->save();
    }
    return $i;
  }

  public static function queueFromUrl($url)
  {
    
    $i = self::createImageFromUrl($url);
    if($i->wasRecentlyCreated)
    {
      ProcessImageJob::dispatch($i);
    }
    return $i;
  }
  
  public static function fromUrl($url)
  {
    $i = self::createImageFromUrl($url);
    $i->att = $url;
    $i->save();
    return $i;
  }
  
  function getTable()
  {
    return config('easy-attachments.table_name');
  }
  
  public function __construct(array $attributes = array()) {
    $this->hasAttachedFile('att', [
      'styles' => self::styles()
    ]);

    parent::__construct($attributes);
  }
  
  function url($size='thumb')
  {
    return $this->att->url($size);
  }

  function path($size='')
  {
    return $this->att->path($size);
  }
  
  public function should_reprocess()
  {
    return !$this->att_file_name || $this->sizes_md5 != self::style_md5();
  }
  
  public static function style_md5()
  {
    return md5(json_encode(self::styles()));
  }
  
  public static function styles()
  {
    $styles = config('easy-attachments.sizes');
    if(!$styles || count($styles)==0)
    {
      throw new \Exception("No sizes defined for Image class. Are you sure you registered the service provider?");
    }
    return $styles;
  }
  
  function reprocess($force=false)
  {
    if(!$force)
    {
      if(!$this->should_reprocess()) return;
    }
    if(!$this->att->originalFilename())
    {
      $this->att = $this->original_file_name;
    } else {
      $this->att->reprocess();
    }
    $this->save();
  }
  
  static function queue($any)
  {
    $Image = config('easy-attachments.image_class');
    if(is_string($any))
    {
      $i = $Image::queueFromUrl($any);
      return $i;
    }
  }
  
  public static function boot()
  {
    parent::boot();
    static::attachmentBoot();
    static::saving(function($obj) {
      $obj->sizes_md5 = Image::style_md5();
    });
    static::saved(function($obj) {
      if(
        config('easy-attachments.preserve_original_files') && 
        file_exists(dirname($obj->original_file_name)) &&
        !file_exists($obj->original_file_name)
      )
      {
        @copy($obj->att->path('original'), $obj->original_file_name);
      }
    });
  }
}

