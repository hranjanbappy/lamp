<?php include 'db.php'; ?>


<style>
.team-container {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    padding-right: 92px;
    padding-left: 92px;
    background-color: #f0f0f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

/* Container for all member cards */
.members-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    max-width: 1200px;
    width: 100%;
    justify-content: center;
    margin-top: 5rem;
    margin-bottom: 5rem;
}

/* Styling for an individual member card */
.member-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    padding: 15px;
    gap: 15px;
    flex-wrap: wrap;
    padding-top: 50px;
    padding-bottom: 50px;
}

/* Styling for the avatar/image container */
.member-avatar {
    flex-shrink: 0; /* Prevent the image from shrinking */
    width: 100px; /* Fixed width for the image */
    height: 100px; /* Fixed height for the image */
    border-radius: 50%; /* Make it circular */
    overflow: hidden; /* Hide overflow for circular shape */
    border: 1px solid #ddd; /* Light border */
}

.member-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Cover the area without distortion */
    display: block;
}

/* Styling for member details text */
.member-details {
    flex-grow: 1; /* Allow details to take remaining space */
}

.member-details h3 {
    margin: 0;
    font-size: 1.1em;
    color: #333;
}

.member-details p {
    margin: 5px 0 0;
    font-size: 0.9em;
    color: #666;
}

.member-details .role {
    font-weight: normal;
    color: #056991; /* Red color for roles */
    margin-bottom: 10px;
}

</style>


<section class="team-container">
    <div class="members-container">
        <?php
        $stmt = $db->query("SELECT * FROM team_members");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
        <div class="member-card">
            <div class="member-avatar">
                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Avatar">
            </div>
            <div class="member-details">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p class="role"><?php echo htmlspecialchars($row['role']); ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>
