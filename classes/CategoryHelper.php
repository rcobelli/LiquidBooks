<?php

use Rybel\backbone\Helper;

class CategoryHelper extends Helper
{

    public function getCategories()
    {
        return $this->query("SELECT * FROM Categories");
    }

    public function getCategoryById($id)
    {
        return $this->query("SELECT * FROM Categories WHERE categoryID = ? LIMIT 1", $id);
    }

    public function createCategory($title)
    {
        return $this->query("INSERT INTO Categories (title) VALUES (?)", $title);
    }

    public function render_newCategoryForm()
    {
        include '../components/newCategoryForm.php';
    }

    public function render_categories()
    {
        echo '<h2 class="mt-4">Categories</h2>';
        echo '<table class="table table-hover"><tbody>';
        $data = $this->getCategories();

        if (empty($data)) {
            echo '<tr><th>No categories found</th></tr>';
        } else {
            foreach ($data as $key) {
                ?>
                <tr class="clickable-row" title="<?php echo $key['title']; ?>">
                    <td><?php echo $key['title']; ?></td>
                </tr>
                <?php
            }
        }
        echo '</tbody></table>';
    }
}
