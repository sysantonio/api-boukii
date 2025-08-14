const fs = require('fs');
const path = require('path');

const planPath = path.join(__dirname, 'relrewrite-plan.json');
const plan = JSON.parse(fs.readFileSync(planPath, 'utf8'));

for (const { file, from, to } of plan) {
  const filePath = path.join(__dirname, '..', file);
  if (!fs.existsSync(filePath)) {
    console.warn(`File not found: ${file}`);
    continue;
  }
  const content = fs.readFileSync(filePath, 'utf8');
  const updated = content.split(from).join(to);
  fs.writeFileSync(filePath, updated, 'utf8');
  console.log(`Rewritten in ${file}: ${from} -> ${to}`);
}
