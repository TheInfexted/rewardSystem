// Volume slider update
document.getElementById('volume')?.addEventListener('input', function() {
    document.getElementById('volumeValue').textContent = Math.round(this.value * 100) + '%';
});

// Save music settings
function saveMusic() {
    const formData = new FormData();
    const musicFile = document.getElementById('music_file').files[0];
    
    if (musicFile) {
        formData.append('music_file', musicFile);
    }
    
    formData.append('volume', document.getElementById('volume').value);
    formData.append('autoplay', document.getElementById('autoplay').checked ? 1 : 0);
    formData.append('loop', document.getElementById('loop').checked ? 1 : 0);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= base_url('admin/landing-page/save-music') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while saving music settings.');
    });
}

// Delete music
function deleteMusic() {
    if (confirm('Are you sure you want to remove the background music?')) {
        fetch('<?= base_url('admin/landing-page/delete-music') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while deleting music.');
        });
    }
}

// Alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Translation
function translation(vlang)
{
    const lang = vlang ? vlang : $(".select-lang").children("option:selected").val();

	$.get("/translate/" + lang, function (data, status) {
		const obj = JSON.parse(data);
		obj.code==1 ? location.reload() : '';
	});
}
// End Translation