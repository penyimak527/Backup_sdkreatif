function parseDMY(tanggalStr) {
	const [day, month, year] = tanggalStr.split('-').map(Number);
	return new Date(year, month - 1, day); // bulan dimulai dari 0 di JS
  }

 