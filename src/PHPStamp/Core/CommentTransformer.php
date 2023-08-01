<?php

namespace PHPStamp\Core;

class CommentTransformer
{
    /**
     * Represent META array as string.
     *
     * @return string
     */
    public function transform(array $comment)
    {
        return json_encode($comment);
    }

    /**
     * Decode string into META array.
     */
    public function reverseTransformer(string $comment)
    {
        return json_decode($comment, true);
    }
}
