<?php $this->layout('layout', ['title' => 'Honeyquip']) ?>

<h1>Honeyquip - SystemAccount</h1>

<h2>User List</h2>
<ul>
    <?php foreach($users as $user): ?>
        <li>
            <a href="/user/collection/<?=$this->e($user->getIdentifier())?>"><?=$this->e($user->getUsername())?></a>
            <?=$this->e($user->getEmail())?>
        </li>
    <?php endforeach ?>
</ul>