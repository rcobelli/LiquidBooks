<?php
/** @var $data array */
?>
<hr/>
<form method="post">
    <h2>Edit Client</h2>
    <div class="form-group">
        <label for="input1">Title</label>
        <input name="title" type="text" class="form-control" id="input1" placeholder="Piece of Shit LLC" value="<?php echo $data['title']; ?>" required>
    </div>
    <input type="hidden" name="submit" value="update">
    <input type="hidden" name="item" value="<?php echo $_REQUEST['item']; ?>">
    <button type="submit" class="btn btn-primary mt-3">Submit</button>
</form>
<button class="btn btn-danger" onclick="window.location = '?action=archive&item=<?php echo $_REQUEST['item']; ?>'">Archive Client</button>
<hr/>
