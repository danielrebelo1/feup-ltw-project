<?php
    declare(strict_types = 1);

    require_once(__DIR__ . '/../utils/session.php');
    session_start();

    require_once(__DIR__ . '/../database/connection.db.php');
    $db = getDatabaseConnection();

    require_once(__DIR__ . '/../templates/common.tpl.php');
    require_once(__DIR__ . '/../templates/ticket.tpl.php');
    require_once(__DIR__ . '/../templates/user_lists.tpl.php');
    require_once (__DIR__ . '/../database/user.class.php');
    require_once (__DIR__ . '/../database/ticket.class.php');
    require_once(__DIR__ . '/../database/tag.class.php');

    if (!Session::isLoggedIn())
        die(header('Location: /pages/login.php'));
    if ($_SESSION['PERMISSIONS'] != 'Admin') 
        die(header('Location: /pages/my_tickets.php'));
    drawHeader(['admin_page'], ['general','admin']);
    ?>
    <main class="content" >
        <div class="page">
        <div class="column">
            <div class = "dropdown" hidden>
                <button name="dropbtn" class="dropbtn" > <i class="fa-sharp fa-solid fa-plus"></i> Create </button>
                <div class="dropdown-content" hidden>
                    <button class="dropdown-button" id="create-department" type="button">Create Department</button>
                    <button class="dropdown-button" id="create-status" type="button">Create Status</button>
                    <button class="dropdown-button" id="create-faq" type="button">Create FAQ</button>
                </div>
            </div>
            <div class="table" hidden>
                <table class="department-table" >
                    <tbody class="table-body">
                        <tr id="table-header">
                            <th>Name of Department</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="column">
            <div id="kpis">
            <h1> Website KPIs </h1>
                <div class="boxes">
                    <div class="box">
                        <h2>Tickets</h2>
                            <div class ="tickets-box">
                                <p id="number-of-tickets-open">Open today:</p>
                                <p id="number-of-tickets-closed">Closed today:</p>
                            </div>
                    </div>
                    <div class="box">
                        <h2>Most used tags</h2>
                        <ul id="most-used-tags">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="form-popup" id="myForm">
            <form action="../actions/create_department.php" id="newDepartmentForm" class="form-container" method="post">
                <h1>New Department</h1>

                <label for="department-name"><b>Department Name</b></label>
                <input type="text" placeholder="Enter new department name" id="department-name" name="department-name" required>
            
                <button type="button" id="submit_create_department" class="btn">Create new department</button>
                <button type="button" id="cancel-creation-department" class="btn cancel">Close</button>
            </form>
        </div>
        <div class="form-popup" id="add-member-popup">
            <form action="../actions/add_user_department.php" id="addtoDepartmentForm" class="form-container" method="post">
                <h1 id="assign-header" >Assign members</h1>
                <label for="department-name"><b>Department</b></label>
                <input type="text" id="department-name" disabled></input>
                <label for="department-name"><b>Members</b></label>
                <select name='members[]' id="assign-dropdown" required multiple>
                </select>
            
                <button type="button" id="submit_assign" class="btn">Assign to department</button>
                <button type="button" id="cancel-assign-department" class="btn cancel">Close</button>
            </form>
        </div>
    </main>
    
    <div id="popup" hidden>
            <h3> Department created </h3>
            <p> Assign members now!</p>
        </div>

    <?php
    drawNavBar($_SESSION['PERMISSIONS']);

    drawFooter();
?>