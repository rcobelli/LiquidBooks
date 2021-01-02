<?php

class ClientHelper extends Helper
{

    public function archiveClient($id)
    {
        return $this->query('UPDATE Clients SET active = 0 WHERE clientID = ?', $id);
    }

    public function getActiveClients()
    {
        return $this->query("SELECT * FROM Clients WHERE active = 1");
    }

    public function getClientByID($id)
    {
        return $this->query("SELECT * FROM Clients WHERE clientID = ? LIMIT 1", $id);
    }

    public function getClientsWithIncome($year)
    {
        return $this->query("SELECT DISTINCT Clients.clientID, Clients.title, Clients.active FROM Clients RIGHT JOIN Transactions USING(clientID) WHERE YEAR(Transactions.date) = ? AND clientID IS NOT NULL ORDER BY clientID", $year);
    }

    public function updateClient($id, $title)
    {
        return $this->query('UPDATE Clients SET title = ? WHERE clientID = ?', $title, $id);
    }

    public function createClient($title)
    {
        return $this->query("INSERT INTO Clients (title) VALUES (?)", $title);
    }

    public function render_newClientForm()
    {
        include '../components/newClientForm.php';
    }

    /** @noinspection PhpUnusedLocalVariableInspection */
    public function render_editClientForm($id)
    {
        $data = $this->getClientByID($id);

        include '../components/editClientForm.php';
    }

    public function render_clients()
    {
        echo '<h2 class="mt-4">Clients</h2>';
        echo '<table class="table table-hover"><tbody>';
        $data = $this->getActiveClients();

        if (empty($data)) {
            echo '<tr><th>No clients, you should get some</th></tr>';
        } else {
            foreach ($data as $key) {
                ?>
                <tr class="clickable-row" onclick="window.location = '?action=update&item=<?php echo $key['clientID']; ?>'" title="<?php echo $key['title']; ?>">
                    <td><?php echo $key['title']; ?></td>
                    <td><?php echo $key['active'] ? "&nbsp;" : "<i>Archived</i>";?></td>
                </tr>
                <?php
            }
        }
        echo '</tbody></table>';
    }
}
