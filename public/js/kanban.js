function onDragStart(e) {
    e
        .dataTransfer
        .setData('text/plain', e.target.id);
}

function onDragOver(event) {
    event.preventDefault();
}

function onDrop(e) {
    const id = e
        .dataTransfer
        .getData('text');

    const draggableElement = document.getElementById(id);

    const dropzone = e.target;
    dropzone.appendChild(draggableElement);

    e
        .dataTransfer
        .clearData();

    // send request to server
    updateItemStatus(draggableElement.getAttribute('url'), dropzone.getAttribute('id'));
}

function updateItemStatus(url, status) {

    // Spin
    let spinner = document.getElementById('spinner');
    spinner.hidden = false;

    // Creating Our XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    let projectId = url.split('/').pop();
    let params = `project=${projectId}&status=${status}`;

    // Making our connection
    xhr.open("GET", url+"?"+params, true);

    // function execute after request is successful
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
        }
        spinner.hidden = true;
    }
    // Sending our request
    xhr.send();
}

function handleChange(e){

    let url = e.target.getAttribute('url');
    const elementId = e.target.getAttribute('id');
    let projectId = url.split('/').pop();

    updateItemStatus(e.target.getAttribute('url'), e.target.value);

    const statusContainer = document.getElementById('item-status');
    statusContainer.innerText = e.target.value;
}