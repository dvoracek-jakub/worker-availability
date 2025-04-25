<?php

// Data storage
$jsonFile = 'statuses.json';
$currentToken = $_GET['token'] ?? null;
$avatarUrl = 'https://avatars.githubusercontent.com/u/{avatar}?v=4';

$statuses = [];
if (file_exists($jsonFile)) {
	$statuses = json_decode(file_get_contents($jsonFile), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$token = $_POST['token'] ?? null;
	$newStatus = $_POST['status'] ?? null;

	foreach ($statuses as &$worker) {
		if ($worker['token'] === $token) {
			$worker['status'] = $newStatus;
			file_put_contents($jsonFile, json_encode($statuses, JSON_PRETTY_PRINT));
			echo htmlspecialchars($newStatus, ENT_QUOTES);
			exit;
		}
	}

	http_response_code(400);
	echo 'Invalid token.';
	exit;
}
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Worker Availability</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

	<div class="container mt-5" style="max-width: 700px;">
		<h1 class="mb-4">Worker Availability</h1>
		<ul class="list-group">
			<?php foreach ($statuses as $worker): ?>
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<div>
						<img src="<?= str_replace('{avatar}', $worker['avatar'], $avatarUrl); ?>" alt="<?= htmlspecialchars($worker['name'], ENT_QUOTES); ?>" class="img-thumbnail rounded-circle me-3" style="width: 50px; height: 50px;">
						<?= htmlspecialchars($worker['name'], ENT_QUOTES); ?>
						<span class="badge ms-2
							<?= $worker['status'] === 'Working' ? 'bg-success' : ($worker['status'] === 'Available for Work' ? 'bg-warning text-dark' :'bg-primary'); ?>" id="statusBadge-<?= $worker['avatar']; ?>">
							<?= htmlspecialchars($worker['status'], ENT_QUOTES); ?>
						</span>

					</div>
					<?php if ($worker['token'] === $currentToken): ?>
						<!-- Dropdown button pouze pro aktuálního workera -->
						<div class="dropdown">
							<button class="btn btn-secondary dropdown-toggle" type="button" id="workerDropdown<?= $worker['avatar']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
								Set status
							</button>
							<ul class="dropdown-menu" aria-labelledby="workerDropdown<?= $worker['avatar']; ?>">
								<li><button class="dropdown-item" onclick="updateStatus('<?= $worker['token']; ?>', 'Working', '<?= $worker['avatar']; ?>')">Working</button></li>
								<li><button class="dropdown-item" onclick="updateStatus('<?= $worker['token']; ?>', 'Available for Work', '<?= $worker['avatar']; ?>')">Available for Work</button></li>
								<li><button class="dropdown-item" onclick="updateStatus('<?= $worker['token']; ?>', 'On Vacation', '<?= $worker['avatar']; ?>')">On Vacation</button></li>
							</ul>
						</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		function updateStatus(token, status, avatar) {
			$.post(window.location.href, { token: token, status: status }, function(response) {
				const badge = $('#statusBadge-' + avatar);
				badge.text(response);
				badge.removeClass('bg-success bg-warning text-dark bg-primary');
				if (status === 'Working') {
					badge.addClass('bg-success');
				} else if (status === 'Available for Work') {
					badge.addClass('bg-warning text-dark');
				} else if (status === 'On Vacation') {
					badge.addClass('bg-primary');
				}
			}).fail(function() {
				alert('Nepodařilo se aktualizovat status.');
			});
		}
	</script>
</body>
</html>