<?php if ($this->book==true): ?>
You've opted to bookmark the login page.  Please book mark the current page.  To return, you can:
<ol>
  <li>Refresh the current page</li>
  <li><a href="/admin">Click here to return</a></li>
</ol>

<a href=""></a>
<?php else: ?>
<h2>Authentication <span class="highlight">Required</span></h2>
<div class="block">
  <?php echo $this->form->showErrors(); ?>
  <form id="message_form" action="/authenticate/login" method="post">
  <table class="form">
    <tr>
      <td class="label"><?php echo $this->form->username->label(); ?></td>
      <td><?php echo $this->form->username->field(); ?></td>
    </tr>
    <tr>
      <td class="label"><?php echo $this->form->password->label(); ?></td>
      <td><?php echo $this->form->password->field(); ?></td>
    </tr>
  </table>
  <button name="submit">Login</button>
  </form>
</div>
<?php endif; ?>