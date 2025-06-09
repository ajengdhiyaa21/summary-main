<?php
function getNextTopLevelNumbering($conn, $kategori) {
    $sql = "SELECT nomor FROM laporan WHERE kategori = ? AND (parent_id IS NULL OR parent_id = 0) ORDER BY CAST(nomor AS UNSIGNED) DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    $nomor = '1';
    if ($row = $result->fetch_assoc()) {
        if (preg_match('/^(\d+)$/', $row['nomor'], $matches)) {
            $nomor = (string)(intval($matches[1]) + 1);
        }
    }
    $stmt->close();
    return $nomor;
}

function incrementLetterSuffix($suffix) {
    $length = strlen($suffix);
    $lastChar = $suffix[$length - 1];
    if ($lastChar !== 'Z') {
        return substr($suffix, 0, $length - 1) . chr(ord($lastChar) + 1);
    } else {
        if ($length == 1) {
            return 'AA';
        } else {
            return incrementLetterSuffix(substr($suffix, 0, $length - 1)) . 'A';
        }
    }
}

function getNextChildNumbering($conn, $parent_id) {
    $sql = "SELECT nomor FROM laporan WHERE parent_id = ? ORDER BY nomor DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $nomor = '';
    if ($row = $result->fetch_assoc()) {
        $lastNomor = $row['nomor'];
        if (preg_match('/([A-Z]+)$/', $lastNomor, $matches)) {
            $lastSuffix = $matches[1];
            $base = substr($lastNomor, 0, -strlen($lastSuffix));
            $nextSuffix = incrementLetterSuffix($lastSuffix);
            $nomor = $base . $nextSuffix;
        } else {
            // First child
            $sqlParent = "SELECT nomor FROM laporan WHERE id = ?";
            $stmtParent = $conn->prepare($sqlParent);
            $stmtParent->bind_param("i", $parent_id);
            $stmtParent->execute();
            $resultParent = $stmtParent->get_result();
            $rowParent = $resultParent->fetch_assoc();
            $parentNomor = $rowParent['nomor'] ?? '';
            $stmtParent->close();
            $nomor = $parentNomor . 'A';
        }
    } else {
        // No children yet
        $sqlParent = "SELECT nomor FROM laporan WHERE id = ?";
        $stmtParent = $conn->prepare($sqlParent);
        $stmtParent->bind_param("i", $parent_id);
        $stmtParent->execute();
        $resultParent = $stmtParent->get_result();
        $rowParent = $resultParent->fetch_assoc();
        $parentNomor = $rowParent['nomor'] ?? '';
        $stmtParent->close();
        $nomor = $parentNomor . 'A';
    }
    $stmt->close();
    return $nomor;
}

function resequenceNumbering($conn, $kategori, $parent_id = null) {
    if ($kategori === null) return;

    if ($parent_id === null) {
        $sql = "SELECT id FROM laporan WHERE kategori = ? AND (parent_id IS NULL OR parent_id = 0) ORDER BY CAST(nomor AS UNSIGNED) ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kategori);
    } else {
        $sql = "SELECT id FROM laporan WHERE parent_id = ? ORDER BY nomor ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parent_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $counter = 1;
    $currentSuffix = '';
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        if ($parent_id === null) {
            $newNomor = (string)$counter;
        } else {
            if ($counter == 1) {
                $currentSuffix = 'A';
            } else {
                $currentSuffix = incrementLetterSuffix($currentSuffix);
            }
            // Get parent nomor
            $sqlParent = "SELECT nomor FROM laporan WHERE id = ?";
            $stmtParent = $conn->prepare($sqlParent);
            $stmtParent->bind_param("i", $parent_id);
            $stmtParent->execute();
            $resultParent = $stmtParent->get_result();
            $rowParent = $resultParent->fetch_assoc();
            $parentNomor = $rowParent['nomor'] ?? '';
            $stmtParent->close();

            $newNomor = $parentNomor . $currentSuffix;
        }
        $stmtUpdate = $conn->prepare("UPDATE laporan SET nomor = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $newNomor, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Recursively resequence children
        resequenceNumbering($conn, $kategori, $id);

        $counter++;
    }
    $stmt->close();
}
?>
