function doPost(e) {
  try {
    if (!e.postData || !e.postData.contents) {
      throw new Error("Tidak ada data POST.");
    }

    const data = JSON.parse(e.postData.contents);
    const action = (data.action || "add").toString().trim();

    // Jika action adalah upload, panggil fungsi upload
    if (action === "upload") {
      return handleUpload(data);
    }

    // Jika action adalah updateStatus, panggil fungsi update status
    if (action === "updateStatus") {
      return handleUpdateStatus(data);
    }

    // Default: handle input konten
    return handleInputKonten(data);

  } catch (error) {
    return ContentService
      .createTextOutput(JSON.stringify({
        status: "error",
        message: error.message
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

function handleInputKonten(data) {
  const bulan = (data.bulan || "").toString().trim();
  const minggu = (data.minggu || "").toString().trim();
  const jenis = (data.jenis || "").toString().trim();
  const isi = (data.isi || "").toString().trim();
  const status = (data.status || "").toString().trim();

  const sheet = SpreadsheetApp
    .getActiveSpreadsheet()
    .getSheetByName("Monitoring Content");

  if (!sheet) {
    throw new Error("Sheet 'Monitoring Content' tidak ditemukan.");
  }

  const values = sheet.getDataRange().getValues();
  let targetRow = 0;
  let targetCol = 0;

  for (let i = 0; i < values.length; i++) {
    if (values[i][0] == jenis) {
      targetRow = i + 1;
      break;
    }
  }
  if (targetRow === 0) {
    throw new Error("Jenis konten tidak ditemukan di kolom A.");
  }

  let startCol = -1;
  for (let col = 0; col < values[0].length; col++) {
    if (values[0][col] == bulan) {
      startCol = col;
      break;
    }
  }
  if (startCol === -1) {
    throw new Error("Bulan tidak ditemukan di baris pertama.");
  }

  let endCol = values[0].length;
  for (let col = startCol + 1; col < values[0].length; col++) {
    if (values[0][col] != "") {
      endCol = col;
      break;
    }
  }

  for (let col = startCol; col < endCol; col++) {
    if (values[1][col] == minggu) {
      targetCol = col + 1;
      break;
    }
  }
  if (targetCol === 0) {
    throw new Error("Minggu tidak ditemukan pada bulan yang dipilih.");
  }

  const cell = sheet.getRange(targetRow, targetCol);

  const oldValue = cell.getDisplayValue();
  let links = [];
  if (oldValue && oldValue.trim() !== "") {
    links = oldValue.split("\n").map(line => line.trim()).filter(line => line !== "");
  }
  const newLinks = isi.split("\n")
    .map(link => link.trim())
    .filter(link => link !== "");
  links = [...new Set(links.concat(newLinks))];

  const finalText = links.join("\n");
  let richTextBuilder = SpreadsheetApp.newRichTextValue().setText(finalText);
  let start = 0;
  links.forEach(link => {
    const end = start + link.length;
    richTextBuilder.setLinkUrl(start, end, link);
    start = end + 1;
  });
  cell.setRichTextValue(richTextBuilder.build());

  const background = getStatusColor(status);
  if (background) {
    cell.setBackground(background);
  }

  cell.setWrapStrategy(SpreadsheetApp.WrapStrategy.WRAP);

  return ContentService
    .createTextOutput(JSON.stringify({ status: "success" }))
    .setMimeType(ContentService.MimeType.JSON);
}

function handleUpdateStatus(data) {
  const bulan = (data.bulan || "").toString().trim();
  const minggu = (data.minggu || "").toString().trim();
  const jenis = (data.jenis || "").toString().trim();
  const status = (data.status || "").toString().trim();

  const sheet = SpreadsheetApp
    .getActiveSpreadsheet()
    .getSheetByName("Monitoring Content");

  if (!sheet) {
    throw new Error("Sheet 'Monitoring Content' tidak ditemukan.");
  }

  const values = sheet.getDataRange().getValues();
  let targetRow = 0;
  let targetCol = 0;

  for (let i = 0; i < values.length; i++) {
    if (values[i][0] == jenis) {
      targetRow = i + 1;
      break;
    }
  }
  if (targetRow === 0) {
    throw new Error("Jenis konten tidak ditemukan di kolom A.");
  }

  let startCol = -1;
  for (let col = 0; col < values[0].length; col++) {
    if (values[0][col] == bulan) {
      startCol = col;
      break;
    }
  }
  if (startCol === -1) {
    throw new Error("Bulan tidak ditemukan di baris pertama.");
  }

  let endCol = values[0].length;
  for (let col = startCol + 1; col < values[0].length; col++) {
    if (values[0][col] != "") {
      endCol = col;
      break;
    }
  }

  for (let col = startCol; col < endCol; col++) {
    if (values[1][col] == minggu) {
      targetCol = col + 1;
      break;
    }
  }
  if (targetCol === 0) {
    throw new Error("Minggu tidak ditemukan pada bulan yang dipilih.");
  }

  const cell = sheet.getRange(targetRow, targetCol);
  const background = getStatusColor(status);
  if (background) {
    cell.setBackground(background);
  }

  return ContentService
    .createTextOutput(JSON.stringify({ status: "success" }))
    .setMimeType(ContentService.MimeType.JSON);
}

function handleUpload(data) {
  var output = {
    status: 'error',
    message: 'Request tidak valid',
    uploaded: []
  };

  try {
    var folderId = data.folderId || extractFolderId(data.folderUrl);
    var files = data.files;

    if (!folderId) {
      throw new Error('folderId atau folderUrl tidak ditemukan.');
    }

    if (!files || !Array.isArray(files) || files.length === 0) {
      throw new Error('Tidak ada file yang dikirim.');
    }

    var folder = DriveApp.getFolderById(folderId);
    if (!folder) {
      throw new Error('Folder Drive tidak ditemukan atau tidak punya akses.');
    }

    files.forEach(function(file) {
      if (!file.name || !file.base64) {
        return;
      }

      var mimeType = file.mimeType || 'application/octet-stream';
      var decoded = Utilities.base64Decode(file.base64);
      var blob = Utilities.newBlob(decoded, mimeType, file.name);
      var created = folder.createFile(blob);

      output.uploaded.push({
        name: created.getName(),
        id: created.getId(),
        url: created.getUrl(),
        mimeType: created.getMimeType()
      });
    });

    output.status = 'success';
    output.message = 'File berhasil diunggah ke Google Drive.';
  } catch (error) {
    output.status = 'error';
    output.message = error.message || String(error);
  }

  return ContentService
    .createTextOutput(JSON.stringify(output))
    .setMimeType(ContentService.MimeType.JSON);
}

function extractFolderId(url) {
  if (!url) {
    return null;
  }
  var match = url.match(/[-\w]{25,}/);
  return match ? match[0] : null;
}

function getStatusColor(status) {
  const normalized = status.toString().toLowerCase();
  if (normalized === "belum progres") {
    return "#FF0000"; // merah
  }
  if (normalized === "on progres") {
    return "#FF9900"; // oren
  }
  if (normalized === "ready to upload" || normalized === "ready to post") {
    return "#00FF2A"; // hijau
  }
  return null;
}