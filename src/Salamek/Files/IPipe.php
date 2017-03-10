<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;

/**
 * Interface IPipe
 * @package Salamek\Files
 */
interface IPipe
{
    /**
     * @param null $file
     * @return mixed
     */
    public function request($file = null);
}