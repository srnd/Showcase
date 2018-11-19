<?php

namespace Showcase\Services;

use Showcase\Models;
use Showcase\Exceptions;
use Showcase\Jobs\QueueableClosure;
use Symfony\Component\HttpFoundation\File\File;

class Photos
{
    const Prefix = 'showcase/photos/';
    const Sizes = [ // Simpler until needed, but should probably be passed into Host() eventually.
        'l' => 1920,
        'm' => 800,
        's' => 256
    ];

    /**
     * Uploads a file to the CDN (and enqueues a rescaling/compressing job for other file sizes).
     *
     * Note: uploaded files may not be available immediately. Additionally, because this relies on several APIs, you
     * may want to call this service asynchronously (however, if you do so, be sure Laravel doesn't delete the temp
     * file after the request closes).
     *
     * @param   File        $file       The uploaded file, using symphony.
     * @param   callable    $callable   Method to call when complete, taking URLs as input.
     * @return  string[]                Associative array of file URLs at different sizes ([o, l, m, s]).
     */
    public static function Host(File $file, callable $onComplete)
    {
        $filePath = $file->move(sys_get_temp_dir(), str_random(15))->getPathname();

        \Queue::push(new QueueableClosure(function() use ($filePath, $onComplete)
        {
            $file = new File($filePath);
            set_time_limit(120);
            $s3 = \Storage::disk('s3');
            $ext = $file->guessExtension();
            if ($ext === 'jpeg') $ext = 'jpg';

            if (!in_array($ext, ['jpg', 'png']))
                throw new Exceptions\UploadFormat("Only JPG- and PNG-format files are supported.");

            do {
                $baseName = Naming::MetalGearSolid().'-'.substr(md5(rand()), 0, 8);
                $oName    = sprintf("%s_%s.%s", $baseName, 'o', $ext);

                // Generate an array of file names for each resize.
                $resizedNames = self::Sizes;
                array_walk($resizedNames, function (&$v, $k) use($baseName) {
                    $v = sprintf("%s_%s.%s", $baseName, $k, 'jpg');
                });
            } while($s3->exists(self::Prefix.$oName));

            // Upload the original
            $s3->putFileAs(self::Prefix, $file, $oName, 'public');
            unlink($filePath);

            // Create resized photos and save them to s3
            foreach ($resizedNames as $size => $resizedName) {
                self::Resize(self::Prefix.$oName, self::Prefix.$resizedName, self::Sizes[$size]);
            }

            // Return URLs
            $result = $resizedNames;
            $result['o'] = $oName;
            array_walk($result, function(&$resizedName, $size) {
                $resizedName = config('filesystems.disks.s3.url').self::Prefix.$resizedName;
            });

            $onComplete($result);
        }));
    }

    /**
     * Creates a zip file of all photos from the event.
     *
     * @param Models\Event  $event      The event to save photos from.
     * @param string        $emailTo    Who to send the zip file to.
     */
    public static function CreateZip(Models\Event $event, string $emailTo)
    {
        \Queue::push(new QueueableClosure(function() use ($event, $emailTo) {
            // Make a temporary directory
            $zipName = tempnam(sys_get_temp_dir(), '').'.zip';

            // Create Zip
            $zip = new \ZipArchive();
            $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            // Download photos
            foreach ($event->Photos as $photo) {
                if (!$photo->UrlLarge) continue;

                $fileName = basename(parse_url($photo->UrlLarge, PHP_URL_PATH));
                $zip->addFromString('general/'.$fileName, file_get_contents($photo->UrlLarge));
            }

            // Download teams
            foreach ($event->Teams as $team) {
                if (!$team->PhotoUrlLarge) continue;

                $fileName = preg_replace('/[^\w-]/', '', $team->Name).'.jpg';
                $zip->addFromString('teams/'.$fileName, file_get_contents($team->PhotoUrlLarge));
            }

            // Save the zip file
            $zip->close();

            // Upload the zip file
            $zipUploadedName = sprintf('export_%s_%s_%s.zip', $event->Id, rand(0,65535), time());
            $zipUrl = config('filesystems.disks.s3.url').self::Prefix.$zipUploadedName;

            $s3 = \Storage::disk('s3');
            $s3->putFileAs(self::Prefix, new File($zipName), $zipUploadedName, 'public');
            unlink($zipName);


            // Send email
            \Mail::send('email-archive', ['event' => $event, 'link' => $zipUrl], function ($m) use ($event, $emailTo) {
                $m->from('team@srnd.org', 'SRND');
                $m->to($emailTo, $emailTo)->subject('Showcase Export for CodeDay '.$event->Name);
            });
        }));
    }

    /**
     * Deletes a file from s3.
     *
     * @param string $originalFile  The URL of the file to delete (must be from a call to Host).
     * @return void
     */
    public static function Delete(string $originalFile)
    {
        $urlPrefix = config('filesystems.disks.s3.url');
        if (substr($originalFile, 0, strlen($urlPrefix)) !== $urlPrefix)
            throw new \Exception("URL is not a hosted photo");

        $photoPath = substr($originalFile, strlen($urlPrefix));
        \Storage::disk('s3')->delete($photoPath);
    }

    /**
     * Resizes a file stored in the default s3 bucket, and saves the resized version into the default s3 bucket.
     *
     * Uses Kraken.io, and automatically compresses the file (using lossy JPG compression).
     *
     * @param   string  $src        The source file-path in s3.
     * @param   string  $dst        The destination file-path in s3.
     * @param   int     $longEdge   The max size for an edge of the photo.
     * @return  void
     */
    public static function Resize(string $src, string $dst, int $longEdge)
    {
        $s3 = \Storage::disk('s3');
        $kraken = new \Kraken(config('kraken.key'), config('kraken.secret'));

        // TODO(@tylermenezes): Check for errors and convert to exceptions
        $kraken->url([
            'url'           => $s3->url($src),
            'lossy'         => true,
            'wait'          => true,
            'format'        => 'jpeg',
            'resize'        => [
                'strategy'  => 'auto',
                'width'     => $longEdge,
                'height'    => $longEdge
            ],
            's3_store'      => [
                'path'      => $dst,
                'key'       => config('filesystems.disks.s3.key'),
                'secret'    => config('filesystems.disks.s3.secret'),
                'bucket'    => config('filesystems.disks.s3.bucket'),
                'region'    => config('filesystems.disks.s3.region'),
                'acl'       => 'public_read',
            ]
        ]);
    }
}
