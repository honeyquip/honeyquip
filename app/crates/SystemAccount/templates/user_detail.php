<?php $this->layout('layout', ['title' => 'Honeyquip']) ?>

<h1>Honeyquip - SystemAccount</h1>

<h2>User Detail</h2>
<?=$this->e($user->getUsername())?>, <?=$this->e($user->getEmail())?>
