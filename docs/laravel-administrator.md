## Integrating with Laravel Administrator

Do you love [Laravel Administrator](https://github.com/FrozenNode/Laravel-Administrator) as much as I do? Sweet. Here's how you do it.

First, familiarize yourself with the `[location](http://administrator.frozennode.com/docs/field-type-image)` attribute of upload fields in Laravel Administrator.

### Step 1: Choose ONE location where Laravel Administrator will upload your files.

In `config/images.php`, there is an `la_path` that can be configured. The default is fine, but if you want to change it you may. Use the same location for ALL models in Laravel Administrator. Laravel Stapler Images will look in this config path for any uploads being saved. I suggest adding a `.gitkeep` to the path.

### Step 2: Configure your Laravel Administrator model, being careful to use the `config()` path you chose in Step 1.

Configure `config/administrator/<your model>.php` as follows:
  
    <?php
    
    return array(
      
      'title' => 'Users',
      
      'single' => 'User',
      
      'model' => 'App\User',
      
      /**
       * The display columns
       */
      'columns' => array(
        'id',
        'avatar_image_id' => array(
          'title' => 'Avatar',
          'output'=>function($id) {
            if(!$id) return '';
            $i = \Image::find($id);
            return "<img src='{$i->url('admin')}?r={$i->updated_at->timestamp}' width=50/>";
          },
        ),    
      ),
      
      /**
       * The editable fields
       */
      'edit_fields' => array(
        'avatar_image_la'=>[
          'title'=>'Avatar',
          'type'=>'image',
          'location'=>config('easy-attachments.la_path').'/',
        ]
        
      ),
      
    );

### Step 3: Add extra JSON attributes to your Eloquent model via `$appends`.

Recall our User model above contained an `avatar_image_id` field, and that we can use `$user->avatar_image` to access it.

    class User
    {
      use AttachmentTrait;
    }

To make sure Laravel Administrator sees it, we must modify the model just a bit:

    class User
    {
      use AttachmentTrait;
      
      protected $appends = ['avatar_image_la'];
    }

The `_la` suffix indicates that this is a Laravel Administrator file attachment field. 

That's it! Now you have images from Laravel Administrator!