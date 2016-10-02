<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Files;


use Salamek\Files\Models\IFile;

interface IPipe
{
    public function request(IFile $file = null);
}