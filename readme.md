# Laravel Easy Attachments

Laravel Easy Attachments makes attaching images and files to Eloquent models a breeze. Laravel Easy Attachments recognizes both regular document attachments and images. When images are attached, Laravel Easy Attachments will pre-generate multiple image preview sizes, optionally cropping or maintaining aspect ratio, and store them wherever you want - even on a cloud provider like S3. Image previews help you to serve the right size image for your use case. 

Attachments are managed in a single database table and duplicate attachments are handled efficiently.

Laravel Easy attachments stands on the shoulders of giants. Gratitude in particular to [codesleeve/laravel-stapler](https://github.com/CodeSleeve/laravel-stapler) and [codesleeve/stapler](https://github.com/CodeSleeve/stapler).

## Features

* Easily attach images to Eloquent models 
* Automatically generate various image preview sizes sizes
* All attachments stored in a single, normalized table with duplicate detection
* Background queue support
* Eloquent magic methods to make working with attachments so easy you'll weep tears of joy

## Installation

Install

    composer require benallfree/laravel-easy-attachments

Add the service providers to `config/app.php`

    BenAllfree\LaravelEasyAttachments\ServiceProvider::class,
    Codesleeve\LaravelStapler\Providers\L5ServiceProvider::class,

Run migrations

    php artisan migrate

## Quickstart

Suppose your `User` model has an `avatar_id` column in it.

First, add an `Attachable` to your model:

    use BenAllfree\LaravelEasyAttachments\Attachable;
    
    class User
    {
      use Attachable;
    }

Now, you can attach documents and images:

    $user->avatar = '/path/to/avatar.jpg';

And get the URL to any image size (by default, `tiny`, `admin`, `thumb`, `medium`, `featured`, `large`)

    echo $user->avatar->url('thumb');

Have fun!

## Basic Usage

### Attaching a Document

Suppose we have a file, `/tmp/foo.doc`. When we create an attachment, the file will automatically be copied into a permanent storage location. `AttachmentFactory` will recognize the document type and return an `Attachment` object.

    use Illuminate\Http\File;
    use BenAllfree\LaravelEasyAttachments\AttachmentFactory;
    
    $file = new File('/tmp/foo.doc');
    
    // Either of these will work
    $att = AttachmentFactory::create($file);
    $att = AttachmentFactory::create($file->path());
    
    echo $att->id;          // The database ID of this attachment - use it however you like
    echo $att->url();       // Public URL to the attachment
    echo $att->path();      // Private file system path to the attachment

### Attaching an Image

Suppose we have a file, `/tmp/foo.jpg`. `AttachmentFactory` will recognize the document type and return an `Image` object, which is almost the same as `Attachment`, except that it knows how to generate multiple image sizes, cache them, and retrieve them.

    use Illuminate\Http\File;
    use BenAllfree\LaravelEasyAttachments\AttachmentFactory;
    
    $file = new File('/tmp/foo.jpg');
    
    // Either of these will work
    $att = AttachmentFactory::create($file);
    $att = AttachmentFactory::create($file->path());
    
    echo $att->id;          // The database ID of this attachment - use it however you like
    echo $att->url('thumb');       // Public URL to the thumb-sized version of the image
    echo $att->url('large');       // Public URL to the large-sized version of the image
    echo $att->path('thumb');      // Private file system path to the thumb-sized version of the image

The following sizes exist by default, from `config/easy-attachments.php`:

    'large' => '640x640#',
    'featured' => '585x585#',
    'medium' => '400x400#',
    'thumb' => '180x180#',
    'admin' => '100x100#',
    'tiny' => '75x75#',

### Attaching a URL

URLs work the same way, only they are fetched first.

    use BenAllfree\LaravelEasyAttachments\AttachmentFactory;
    
    $url = "http://img.wennermedia.com/480-width/rick-astley-fafdb413-f264-4d61-8671-6c93bda94591.jpg');
    
    // It will know this is an image, fetch it, generate all image preview sizes, and return the proper attachment type
    $att = AttachmentFactory::create($url);
    
    echo $att->id;          // The database ID of this attachment - use it however you like
    echo $att->url('thumb');       // Public URL to the thumb-sized version of the image
    echo $att->url('large');       // Public URL to the large-sized version of the image
    echo $att->path('thumb');      // Private file system path to the thumb-sized version of the image

### Using Queues

URLs can take a while to download. Image attachments need to generate multiple preview sizes, and that can take time. If you don't need the data immediately, and you don't mind waiting, you can do it this way:

    use BenAllfree\LaravelEasyAttachments\AttachmentFactory;
    
    $url = "http://img.wennermedia.com/480-width/rick-astley-fafdb413-f264-4d61-8671-6c93bda94591.jpg');
    
    // AttachmentFactory will give you back an Attachment or Image object using its best guess, 
    // but it won't process it immediately. Instead, it will be added to the worker queue. If 
    // you give it a file path, it will expect that file path to be accessible from the queue,
    // so be careful about that.
    $att = AttachmentFactory::queue($url);
    
    echo $att->id;              // The database ID is all we have
    echo $att->url('thumb');    // This will return NULL because it has not been processed yet
    echo $att->path('thumb');   // This will return NULL because it has not been processed yet

### Attaching to a Model (the hard way and the easy way)

You can create attachments using the code above, that's super fun. But know what's even fun'r? Using some Eloquent model magic. 

Let's add an `avatar` and `resume` to our `User` model.

First, create a migration. In this case, let's do a simple `belongsTo` relationship.

    Schema::table('users', function (Blueprint $table) {
      $table->integer('avatar_id');
      $table->integer('resume_id');
    });

#### The Hard Way

You can do it this way if you hate yourself:

    class User
    {
       function resume()
       {
          return $this->belongsTo(\Attachment::class, 'resume_id');
       }
       
       function avatar()
       {
          return $this->belongsTo(\Image::class, 'avatar_id');
       }
    }
    
    $resume = AttachmentFactory::create('/path/to/resume.doc');
    $user->resume_id = $resume->id;
    $avatar = AttachmentFactory::create('/path/to/avatar.jpg');
    $user->avatar_id = $resume->id;
    $user->save();
    
    echo $user->resume->url();
    echo $user->avatar->url('thumb');

#### The Easy Way

But love yourself. Add `Attachable` to the `User` model. Things will get automatically processed and saved on the spot.

    use BenAllfree\LaravelEasyAttachments\Attachable;
    
    class User
    {
      use Attachable;
    }
    
    $user->resume = '/path/to/resume.doc';
    $user->avatar = '/path/to/avatar.jpg';
    
    echo $user->resume->url();
    echo $user->avatar->url('thumb');

#### The Easy AND Fast Way

If you want to delay/offload the processing while still using magical methods, here's how to do it:

    $user->resume = AttachmentFactory::queue('/path/to/resume.doc');
    $user->avatar = AttachmentFactory::queue('/path/to/avatar.jpg');
    

    echo $user->resume->id;           // Will work
    echo $user->avatar->id;           // Will work
    echo $user->resume->url();        // Won't work yet!
    echo $user->avatar->url('thumb'); // Won't work yet!


## Forms and Input

Given a field named `avatar` like above...

In the view:

    {!! Form::open(['method'=>'post', 'files'=>true]) !!}
    {!! Form::file('avatar_image') !!}
    {!! Form::submit('Update') !!}
    {!! Form ::close() !!}

### In the controller

    function do_postback(Request $r)
    {
      $u = Auth::user();
      $u->update($r->input());
      $u->update($r->files()); // This is the important one
    }

## Advanced Configuration

Eject the config files:

    php artisan vendor:publish --tag=laravel-easy-attachments

`config/easy-attachments.php` is where you control settings for this package.

`sizes` controls what image sizes are generated when you attach an image. Including a '#' at the end of the dimension will cause it to zoom and crop on center, guaranteeing that the image size is exactly what you specify. Omitting the '#' will maintain aspect ratio, making the image as big as possible while still fitting in the dimensions specified.

    $sizes = [
      'large' => '640x640#',
      'featured' => '585x585#',
      'medium' => '400x400#',
      'thumb' => '180x180#',
      'admin' => '100x100#',
      'tiny' => '75x75#',
    ];


`table_name` controls the name of the table used to store the attachments. If you set this before running migrations, the migrations will honor this table name.

`la_path` is the file path where Laravel Administrator file sare stored. See the Laravel Administrator section for more details about how to use this.

`image_class` and `attachment_class` control what class is instantiated for images and plain attachments. If you override the default `Image` and `Attachment` classes and want Laravel Easy Images to instantiate objects using your superclasses, specify them here.

`do_not_delete` is an array of file paths which, if matched, will not be deleted on attachment. The default behavior of Stapler is to move files rather than copy them. This prevents deletion. [fnmatch](http://php.net/manual/en/function.fnmatch.php) is used to evaluate path matches.

Optionally add an alias for the `\Image` and `\Attachment` classes in `config/app.php`

    'Image' => BenAllfree\LaravelEasyAttachments\Image::class,
    'Attachment' => BenAllfree\LaravelEasyAttachments\Attachment::class,

## Memory

Image processing can take a significant amount of memory, more the PHP is normally configured to use.

If you are processing images inline (during the web request), you will probably need to modify your memory limit in `php.ini`:

    memory_limit=8096M 

However, we recommend that you use Laravel Queues to process your images out of band. In that case, you'll need to make sure your worker processes, which run using the PHP CLI, have sufficient memory. The PHP CLI typically uses a different `php.ini` than the PHP web process. To find it:

    $ php -i | grep ini
    ...
    Loaded Configuration File => /Applications/MAMP/bin/php/php7.1.1/conf/php.ini

## Caching and Performance

By default, `Image::fromUrl($url)` will check `$url` against the `original_file_name` column in the images table and will only fetch the image the first time it has to. If you want to force it, use `fromUrl($url, true)`.

## Reprocessing Images

If you change sizes of images, you will need to reprocess existing images.

    php artisan images:reprocess

## Securing Images

If you want your images to be served from within secure routes instead of directly available from the `public` folder, make these modifications:

Create the following file:

    /storage/uploads/.gitkeep

Eject Laravel Stapler configs. In `config/laravel-stapler/filesystem.php` to change where the images are stored (outside the webroot):

	'path' => ':app_root/storage/uploads:url',

Then, create a route like this and add whatever security you need:

    Route::get('/images/{id}/{size}', function($id,$size) {
      $image = \Image::find($id);
      if(!$image)
      {
        App::abort(404);
      }
      
      $response = Response::make(
         File::get($image->image->path($size)), 
         200
      );
      $response->header(
        'Content-type',
        'image/jpeg'
      );
      return $response;
    });

## Integrating with Laravel Administrator

Do you love [Laravel Administrator](https://github.com/FrozenNode/Laravel-Administrator) as much as I do? Sweet. Here's how you do it.

First, familiarize yourself with the `[location](http://administrator.frozennode.com/docs/field-type-image)` attribute of upload fields in Laravel Administrator.

**Step 1: Choose ONE location where Laravel Administrator will upload your files.**

In `config/images.php`, there is an `la_path` that can be configured. The default is fine, but if you want to change it you may. Use the same location for ALL models in Laravel Administrator. Laravel Stapler Images will look in this config path for any uploads being saved. I suggest adding a `.gitkeep` to the path.

**Step 2: Configure your Laravel Administrator model, being careful to use the `config()` path you chose in Step 1.**

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
        'avatar_la'=>[
          'title'=>'Avatar',
          'type'=>'image',
          'location'=>config('easy-attachments.la_path').'/',
        ]
        
      ),
      
    );

**Step 3: Add extra JSON attributes to your Eloquent model via `$appends`.**

Recall our User model above contained an `avatar_id` field, and that we can use `$user->avatar` to access it.

    class User
    {
      use Attachable;
    }

To make sure Laravel Administrator sees it, we must modify the model just a bit:

    class User
    {
      use Attachable;
      
      protected $appends = ['avatar_la'].;
    }

The `_la` suffix indicates that this is a Laravel Administrator file attachment field. 

That's it! Now you have images from Laravel Administrator!

## Advanced Configuration of Laravel Stapler

You may find yourself wanting more fine-grained control over how images are stored. For this, you must modify the underlying Laravel Stapler settings directly. 

First, eject the Laravel Stapler config:

    php artisan vendor:publish --provider="Codesleeve\LaravelStapler\Providers\L5ServiceProvider"

Take a look at the config files in `config/laravel-stapler`. If you're not familiar with the config files, see the [basic Stapler config docs](https://github.com/CodeSleeve/stapler/blob/master/docs/configuration.md).


I like this setting for `config/larave-stapler/filesystem.php`

    'url' => '/i/:id_partition/:style/:filename',

## Workarounds and bugfixes

The core `codesleeve/stapler` package has a couple bugs that may be important to you. To use my forked version of Stapler with the bugs fixed, do the following:

In your composer.json:

    "repositories": [
      {
          "type": "vcs",
          "url": "git@github.com:benallfree/stapler.git"
      }
    "require": {
        "codesleeve/stapler": "dev-master as 1.2.0",
    },

### Hashtag and long URL name fixes

Two problems:

1. If a URL containing a # (hash) is fetched, it will fail to use the proper file extension, this creating MIME type problems.
2. If a URL is too long, it will faile to write to the file system. I fixed this by using shorter names.

### curl/open_basedir bug

If you are on shared hosting, you may find that `open_basedir` is set and `curl` will fail to fetch URLs. To fix that, we have included a patch file with this package.

### rename bug

`codesleeve/stapler` renames files as part of processing URL downloads and using temporary files. If your temporary files are stored on a different volume, there is a [known PHP issue](https://bugs.php.net/bug.php?id=50676) that will cause a Laravel exception. To fix it, `@rename` should be used.
