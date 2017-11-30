function confirmDelete(id){
    var confirmed = confirm('Are you sure you want to delete DBQuery Report Table ' + id + '?');
    return (confirmed) ? true : false;
}
