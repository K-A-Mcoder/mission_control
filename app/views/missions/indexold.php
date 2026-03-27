<?php $layout = 'app'; ?>

<div class="page-titles dark:bg-[#242424] flex items-center justify-between relative border-b border-[#E6E6E6] dark:border-[#444444] flex-wrap z-[1] py-[0.6rem] sm:px-[1.95rem] px-[1.55rem] bg-white">
    <ol class="text-[13px] flex items-center flex-wrap bg-transparent">
        <li>
            <h5 class="sm:text-[17px] text-[15px] mr-8">Teams Page</h5>
        </li>
        <li>
            <a href="javascript:void(0)" class="text-[#828690] dark:text-white text-[13px]">
                <svg class="mb-[3px] mr-1 inline-block" width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="#2C2C2C" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="#2C2C2C" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Teams </a>
        </li>
    </ol>
</div>

<!-- main -->
<div class="container-fluid">
    <div class="row">
        <div class="w-full active-p">
            <div class="flex justify-between items-center mb-6">
                <h4 class="heading">Missions</h4>
                <div>
                    <a href="/missions/create" class="btn btn-primary duration-500 hover:bg-hover-primary py-[5px] px-3 text-[13px] rounded text-white bg-primary leading-[18px] inline-block border border-primary ml-2">
                        + Add Mission
                    </a>
                    <button id="delete-selected" class="btn btn-primary duration-500 hover:bg-hover-primary py-[5px] px-3 text-[13px] rounded text-white bg-primary leading-[18px] inline-block border border-primary ml-2">
                        Delete Selected
                    </button>
                </div>
            </div>
            <div class="card h-auto">
                <div class="card-body p-50px">
                    <div class="overflow-x-auto active-projects style-1 shorting dt-filter exports">
                        <div class="tbl-caption flex items-center justify-between flex-wrap p-2.5">
                        </div>
                        <table id="missions-tbl" class="table ItemsCheckboxSec" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-[13px] py-2.5 pl-4 pr-0 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap style-1">
                                        <div class="form-check custom-checkbox block min-h-[1.3125rem] pl-[1.5em] mb-0.5 text-sm font-semibold ms-0">
                                            <input type="checkbox" class="form-check-input checkAll" id="checkInput" required="">
                                            <label class="form-check-label" for="checkInput">

                                            </label>
                                        </div>
                                    </th>
                                    <th class="text-[13px] py-2.5 px-4 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap text-left">
                                        CODE
                                    </th>
                                    <th class="text-[13px] py-2.5 px-4 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap text-left">
                                        Title
                                    </th>
                                    <th class="text-[13px] py-2.5 px-4 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap text-left">
                                        Classification
                                    </th>
                                    <th class="px-4 py-3">Progress</th>
                                    <th class="text-[13px] py-2.5 px-4 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap text-left">
                                        START - END Dates
                                    </th>
                                    <th class="text-[13px] py-2.5 px-4 bg-[#F0F4F9] text-[#374557] capitalize font-medium bg-none whitespace-nowrap text-left">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($missions as $mission): ?>
                                    <?php
                                    $total     = (int) ($mission['total_tasks'] ?? 0);
                                    $completed = (int) ($mission['completed_tasks'] ?? 0);
                                    $progress  = $total > 0 ? round(($completed / $total) * 100) : 0;

                                    $badge = match ($mission['classification']) {
                                        'SECRET'       => 'bg-danger-light text-danger',
                                        'CONFIDENTIAL' => 'bg-warning-light text-warning',
                                        default        => 'bg-primary-light text-primary',
                                    };
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                                        <td class="px-4 py-3 font-mono text-xs">
                                            <div class='form-check custom-checkbox block min-h-[1.3125rem] pl-[1.5em] mb-0.5 text-sm font-semibold'>
                                                <input type="checkbox" class="form-check-input" id="mission-<?= $mission['id'] ?>" value="<?= htmlspecialchars($mission['id']) ?>">
                                                <label class="form-check-label" for="mission-<?= $mission['id'] ?>"></label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs">
                                            <?= htmlspecialchars($mission['m_code']) ?>
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            <a href="/missions/<?= $mission['id'] ?>"
                                                class="text-primary hover:underline">
                                                <?= htmlspecialchars($mission['title']) ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs font-medium px-2 py-1 rounded <?= $badge ?>">
                                                <?= htmlspecialchars($mission['classification']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 min-w-[120px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-primary h-1.5 rounded-full"
                                                        style="width: <?= $progress ?>%"></div>
                                                </div>
                                                <span class="text-xs text-muted"><?= $progress ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-muted text-xs">
                                            <?= $mission['start_time'] ? date('M d, Y', strtotime($mission['start_time'])) : '—' ?> -
                                            <?= $mission['end_time'] ? date('M d, Y', strtotime($mission['end_time'])) : '—' ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <a href="/missions/<?= $mission['id'] ?>"
                                                    class="text-primary hover:underline text-xs">View</a>

                                                <?php if (has_any_role(['admin', 'manager'])): ?>
                                                    <a href="/missions/<?= $mission['id'] ?>/edit"
                                                        class="text-muted hover:text-dark text-xs">Edit</a>
                                                <?php endif; ?>

                                                <?php if (has_role('admin')): ?>
                                                    <form action="/missions/<?= $mission['id'] ?>/delete" method="POST"
                                                        onsubmit="return confirm('Delete this mission?')">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                            class="text-danger hover:underline text-xs bg-transparent border-0 cursor-pointer p-0">
                                                            Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>