import { fetch_department_api, fetch_tag_api, fetch_user_api } from './fetch_api.js'

export async function loadDepartments(params, select = null) {
    const departmentsSelect = document.getElementById('department-select');
    const departments = await fetch_department_api(params);
    if (departments.length !== 0) {
        for (const department of departments) {
            const option = document.createElement('option');
            option.value = department.DepartmentID;
            option.textContent = department.Name;
            if (select == department.DepartmentID)
                option.selected = true;
            departmentsSelect.appendChild(option);
        }
    } else {
        document.querySelector('#filter-form > label[for="department-select"]').hidden = true;
        departmentsSelect.hidden = true;
    }
}

export async function loadTags(params) {
    const tagsSelect = document.getElementById('tag-select');
    const tags = await fetch_tag_api(params);
    if (tags.length !== 0) {
        for (const tag of tags) {
            const option = document.createElement('option');
            option.value = tag.TagID;
            option.textContent = tag.Name;
            tagsSelect.appendChild(option);
        }
    } else {
        tagsSelect.hidden = true;
        document.querySelector('#filter-form > label[for="tag-select"]').hidden = true;
    }
}

export async function loadAgents(params, select = null) {
    const agentsSelect = document.getElementById('agent-select');
    const agents = await fetch_user_api(params);
    if (agents['agents'].length !== 0) {
        for (const agent of agents['agents']) {
            const option = document.createElement('option');
            option.value = agent['id'];
            option.textContent = '@' + agent['username'];
            if (select == agent['id'])
                option.selected = true;
            agentsSelect.appendChild(option);
        }
    }
}