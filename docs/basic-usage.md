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

But love yourself. Add `AttachmentTrait` to the `User` model. Things will get automatically processed and saved on the spot.

    use BenAllfree\LaravelEasyAttachments\AttachmentTrait;
    
    class User
    {
      use AttachmentTrait;
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

