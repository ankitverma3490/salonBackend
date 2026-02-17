import os

path = r'e:\salon saas\Saloon-Saas-main\backend\api\routes\staff.php'
with open(path, 'r') as f:
    content = f.read()

old_line = '        $query .= " AND s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?)";'
new_line = '        $query .= " AND (s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?) OR s.id NOT IN (SELECT staff_id FROM staff_services))";'

if old_line in content:
    new_content = content.replace(old_line, new_line)
    with open(path, 'w') as f:
        f.write(new_content)
    print("Successfully replaced line.")
else:
    print("Old line not found.")
