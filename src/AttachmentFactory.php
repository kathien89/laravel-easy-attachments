<?php
namespace BenAllfree\LaravelEasyAttachments;

class AttachmentFactory
{
  static public function queue($any)
  {
    $Image = config('easy-attachments.image_class');
    $Attachment = config('easy-attachments.attachment_class');

    if(is_string($any))
    {
      $image_mimes = [
        'image/gif',
        'image/jpeg',
        'image/png',
      ];
      $type = mime_content_type($any);
      if(array_search($type, $image_mimes)!==false)
      {
        $obj = $Image::queue($any);
        return $obj;
      }
      $obj = $Attachment::queue($any);
      return $obj;
    }
    throw new \Exception("Unhandled attachment type {$any}");
  }
  
}