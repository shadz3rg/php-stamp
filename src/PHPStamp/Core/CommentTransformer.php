<?php

namespace PHPStamp\Core;

use PHPStamp\Exception\DecodeException;
use PHPStamp\Exception\EncodeException;

class CommentTransformer
{
    /**
     * Represent META array as string.
     *
     * @param array<string,mixed> $comment
     *
     * @throws EncodeException
     */
    public function transform(array $comment): string
    {
        $output = json_encode($comment, JSON_FORCE_OBJECT);
        if ($output === false) {
            throw new EncodeException();
        }

        return $output;
    }

    /**
     * Decode string into META array.
     *
     * @return array<string,mixed> $comment
     *
     * @throws DecodeException
     */
    public function reverseTransformer(string $comment): array
    {
        /** @var array<string,mixed>|null $output */
        $output = json_decode($comment, true);
        if ($output === null) {
            throw new DecodeException();
        }

        return $output;
    }
}
