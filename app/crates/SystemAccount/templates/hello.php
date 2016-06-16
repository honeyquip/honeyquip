<?php $this->layout('layout', ['title' => 'Honeyquip']) ?>

<h1>Honeyquip</h1>

<h2>Users</h2>
<ul>
    <?php foreach($users as $user): ?>
        <li>
            <?=$this->e($user->getUsername())?>, <?=$this->e($user->getEmail())?>
        </li>
    <?php endforeach ?>
</ul>