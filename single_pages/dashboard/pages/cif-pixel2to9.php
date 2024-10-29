<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var bool $_bookmarked
 * @var Concrete\Core\Filesystem\Element $_breadcrumb
 * @var Concrete\Core\Page\Page $c
 * @var Concrete\Package\Pixel2to9\Controller\SinglePage\Dashboard\Pages\CifPixel2to9 $controller
 * @var Concrete\Core\Application\Service\Dashboard $dashboard
 * @var Concrete\Core\Error\ErrorList\ErrorList $error
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Html\Service\Html $html
 * @var Concrete\Core\Application\Service\UserInterface $interface
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Page\View\PageView $view
 */

?>

<div id="cp29" v-cloak v-on:dragover.prevent="drop($event, true)" v-on:drop.prevent="drop($event, false)">
    <table>
        <tbody>
            <tr class="cp29-io">
                <td colspan="3">
                    <textarea class="cp29-input" v-bind:readonly="busy" v-model.trim="input" placeholder="<?= h(t('Paste here your XML or drop an XML file here.') . "\n\n<concrete5-cif>\n    ...\n<concrete5-cif>") ?>"></textarea>
                </td>
                <td colspan="3">
                    <div v-if="error !== ''" class="alert alert-danger">{{ error }}</div>
                    <div v-else-if="output !== ''">
                        <div class="cp29-output">{{ output }}</div>
                        <div class="text-center">
                        </div>
                    </div>
                 </td>
            </tr>
            <tr class="cp29-btn">
                <td colspan="2" class="text-left text-start">
                    <button class="btn btn-primary" v-bind:disabled="busy" v-on:click.prevent="upload()"><?= t('Upload')?></button>
                </td>
                <td colspan="2" class="text-center">
                    <button class="btn btn-primary" v-bind:disabled="input === '' || busy" v-on:click.prevent="convert()"><?= t('Convert')?></button>
                </td>
                <td colspan="2" class="text-right text-end">
                    <div v-if="error === '' && output !== ''">
                        <button class="btn btn-primary" v-bind:disabled="busy" v-on:click.prevent="copy()"><?= t('Copy')?></button>
                        <button class="btn btn-primary"  v-bind:disabled="busy" v-on:click.prevent="download()"><?= t('Download')?></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>document.addEventListener('DOMContentLoaded', function() {

function readFile(file)
{
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            resolve(reader.result);
        };
        reader.onerror = (error) => {
            reject(error.message || error.toString() || '?');
        };
        reader.readAsText(file);
    });
}

let uploaderInput = null;

new Vue({
    el: '#cp29',
    data() {
        return {
            input: '',
            busy: false,
            error: '',
            output: '',
        };
    },
    mounted() {
        
    },
    methods: {
        async drop(event, preview) {
            if (this.busy) {
                event.dataTransfer.effectAllowed = 'none';
                return;
            }
            if (!event?.dataTransfer) {
                return;
            }
            if (event.dataTransfer.files?.length !== 1 || !event.dataTransfer.files[0]?.size) {
                event.dataTransfer.effectAllowed = 'none';
                return;
            }
            event.dataTransfer.effectAllowed = 'copy';
            if (preview) {
                return;
            }
            this.busy = true;
            try {
                this.input = await readFile(event.dataTransfer.files[0]);
            } catch (e) {
                window.alert(e.message || e.toString() || '?');
            } finally {
                this.busy = false;
            }
        },
        async upload() {
            if (this.busy) {
                return;
            }
            if (uploaderInput === null) {
                uploaderInput = document.createElement('input');
                uploaderInput.type = 'file';
                uploaderInput.accept = 'application/xml'; 
                uploaderInput.style.display = 'none';
                document.body.appendChild(uploaderInput);
                uploaderInput.addEventListener('change', async (event) => {
                    const file = event.target?.files[0];
                    if (!file) {
                        return;
                    }
                    try {
                        this.input = await readFile(file);
                    } catch (e) {
                        window.alert(e.message || e.toString() || '?');
                    }
                });
            }
            uploaderInput.click();
        },
        async convert() {
            if (this.busy) {
                return;
            }
            this.busy = true;
            this.error = '';
            this.output = '';
            try {
                const requestData = new URLSearchParams();
                requestData.append(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('cp29-convert')) ?>);
                requestData.append('cif', this.input);
                const response = await window.fetch(<?= json_encode($view->action('convert')) ?>, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: requestData.toString(),
                });
                const responseData = await response.json();
                if (responseData.error) {
                    if (typeof responseData.error === 'string') {
                        throw new Error(responseData.error);
                    }
                    if (typeof responseData.error.message === 'string') {
                        throw new Error(responseData.error.message);
                    }
                    throw new Error(responseData);
                }
                if (typeof responseData.xml !== 'string') {
                    throw new Error(<?= json_encode(t('Unexpected response from the server')) ?>);
                }
                this.output = responseData.xml;
            } catch (e) {
                this.error = e.message || e.toString() || '?';
            } finally {
                this.busy = false;
            }
        },
        async copy() {
            if (this.busy) {
                return;
            }
            this.busy = true;
            try {
                try {
                    await window.navigator.clipboard.writeText(this.output);
                } catch {
                    const i = document.createElement('textarea');
                    i.style.width = '1px';
                    i.style.height = '1px';
                    i.value = this.output;
                    document.body.appendChild(i);
                    i.select();
                    try {
                        document.execCommand('copy');
                    } finally {
                        document.body.removeChild(i);
                    }
                }
            } catch (e) {
                window.alert(e.message || e.toString() || '?');
            } finally {
                this.busy = false;
            }
        },
        download() {
            const a = document.createElement('a');
            a.style.display = 'none';
            document.body.appendChild(a);
            try {
                const blob = new Blob([this.output], {type: 'application/xml'});
                const url = window.URL.createObjectURL(blob);
                try {
                    a.href = url;
                    a.download = 'concrete5-cif.xml';
                    a.click();
                } finally {
                    window.URL.revokeObjectURL(url);
                }
            } finally {
                document.body.removeChild(a);
            }
        },
    },
});

});</script>
