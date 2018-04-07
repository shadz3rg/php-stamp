<?php

namespace PHPStamp\Core;

class CommentTransformer
{
    /**
     * Represent META array as string.
     *
     * @param array $comment
     * @return string
     */
    public function transform(array $comment)
    {
        return json_encode($comment);
    }

    /**
     * Decode string into META array.
     *
     * @param $comment
     * @return mixed
     */
    public function reverseTransformer($comment)
    {
        return json_decode($comment, true);
    }
}