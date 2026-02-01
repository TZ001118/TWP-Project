/* admin_script.js */

window.addEventListener('DOMContentLoaded', event => {
            
    // 1. 恢复记忆：检查之前是不是收起的
    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        document.body.classList.add('sb-sidenav-toggled');
    }

    // 2. 侧边栏切换按钮逻辑
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            
            // 切换 class
            document.body.classList.toggle('sb-sidenav-toggled');
            
            // 存入记忆
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // 3. 订单详情页：状态下拉框颜色实时切换
    const statusSelect = document.getElementById('statusSelect');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            // 先移除所有可能的颜色类
            this.classList.remove('text-warning', 'border-warning', 'text-success', 'border-success', 'text-danger', 'border-danger');
            
            // 根据选中的值添加对应的颜色
            if (this.value === 'Pending') {
                this.classList.add('text-warning', 'border-warning');
            } else if (this.value === 'Completed') {
                this.classList.add('text-success', 'border-success');
            } else if (this.value === 'Cancelled') {
                this.classList.add('text-danger', 'border-danger');
            }
        });
    }
});