<section class="user-list">
    <?php if (!empty($users)): ?>
        <table>
            <thead>
                <tr>                    
                    <th>Nome</th>
                    <th>Idade</th>
                    <th>Email</th>
                    <th>Profissão</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>                        
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= $row['idade'] ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['profissao']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum usuário cadastrado ainda.</p>
    <?php endif; ?>
</section>