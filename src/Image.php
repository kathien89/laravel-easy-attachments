<?php
namespace BenAllfree\LaravelEasyAttachments;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class Image  extends \Illuminate\Database\Eloquent\Model implements StaplerableInterface 
{
  use EloquentTrait;

  public static function createImageForUrl($url)
  {
    $Image = config('laravel-stapler.easy-images.image_class');
    $i = $Image::whereOriginalFileName($url)->first();
    if(!$i)
    {
      $i = new $Image();
    }
    $i->original_file_name = $url;
    $i->save();
  }

  public static function queueFromUrl($url)
  {
    $i = self::createImageForUrl($url);
    FetchImageJob::dispatch($i);
    return $i;
  }
  
  public static function fromUrl($url)
  {
    $i = self::createImageForUrl($url);
    $i->att = $url;
    $i->save();
    return $i;
  }
  
  function getTable()
  {
    return config('laravel-stapler.easy-attachments.table_name');
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
    return $this->sizes_md5 != self::style_md5();
  }
  
  public static function style_md5()
  {
    return md5(json_encode(self::styles()));
  }
  
  public static function styles()
  {
    $styles = config('laravel-stapler.easy-attachments.sizes');
    if(!$styles || count($styles)==0)
    {
      throw new \Exception("No sizes defined for Image class. Are you sure you registered the service provider?");
    }
    return $styles;
  }
  
  function reprocess($should_check_first=false)
  {
    if($should_check_first)
    {
      if(!$this->should_reprocess()) return;
    }
    $ret = $this->att->reprocess();
    $this->save();
    return $ret;
  }
}

Image::saving(function($obj) {
  $obj->sizes_md5 = Image::style_md5();
});
