<?php

namespace A17\Twill\Services\FileLibrary;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as FilesystemManager;
use Symfony\Component\HttpFoundation\Response;

class S3PrivateToLocal implements FileServiceInterface
{
    /**
     * @var FilesystemManager
     */
    protected $filesystemManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param FilesystemManager $filesystemManager
     * @param Config $config
     */
    public function __construct(FilesystemManager $filesystemManager, Config $config)
    {
        $this->filesystemManager = $filesystemManager;
        $this->config = $config;
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function getUrl($id)
    {
        return "/file/$id";
    }

    public function getFile($path) {
        if ($this->filesystemManager->disk('twill_file_library_local')->exists($path)) {
            return $this->filesystemManager->disk('twill_file_library_local')->response($path);
        } else {
            if ($this->filesystemManager->disk($this->config->get('twill.file_library.disk'))->exists($path)) {
                $this->filesystemManager->disk('twill_file_library_local')->put(
                    $path,
                    $this->filesystemManager->disk($this->config->get('twill.file_library.disk'))->get($path)
                );
                return $this->filesystemManager->disk('twill_file_library_local')->response($path);
            }
        }
        abort(Response::HTTP_NOT_FOUND);
    }
}
