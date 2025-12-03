<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Middleware;

use Andersundsehr\Editara\Service\DataHandlerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Error\Http\UnauthorizedException;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Frontend\Page\PageInformation;

use function array_column;
use function array_key_exists;
use function implode;
use function json_decode;
use function stream_get_contents;

class EditaraPersistenceMiddleware implements MiddlewareInterface
{
    /** @var list<\Andersundsehr\Editara\Dto\EditableResult> */
    public static $editableResults = [];

    public function __construct(
        private readonly Context $context,
        private readonly DataHandlerService $dataHandlerService,
        private readonly TcaSchemaFactory $tcaSchema,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->shouldSaveStuff($request)) {
//            $params = $request->getQueryParams();
//            if (isset($params['editara']) && true) {
//                $handler->handle($request);
//                return new HtmlResponse($this->renderOnlyInputFields());
//            }
            return $handler->handle($request);
        }
        return $this->saveStuff($request); // TODO should we return here, or render the HTML?

        return $handler->handle($request);
    }

    private function saveStuff(ServerRequestInterface $request): ResponseInterface {
        $data = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        $pageUid = $this->getPageInformation($request)->getId();


        foreach($data as $table => $rows) {
            if(!$this->tcaSchema->has($table)) {
                throw new \RuntimeException('Table "' . $table . '" not found in TCA schema');
            }

            foreach($rows ?? [] as $uid => $fields) {
                if ($table === 'editable' && array_key_exists('__languageSyncUid', $fields)) {
                    $languageSyncUid = $fields['__languageSyncUid'];
                    $this->dataHandlerService->setL10nStateForFields($table, (int)$uid, $pageUid,  $languageSyncUid);
                    unset($fields['__languageSyncUid']);
                }

                if(!$fields) {
                    continue;
                }

                // TODO check $fields (permissions?)
                $this->dataHandlerService->updateRow($table, (int)$uid, $fields);
            }
        }
        return new JsonResponse(['success' => true]);
    }

    private function shouldSaveStuff(ServerRequestInterface $request)
    {
        // parameter editara must be set
        $params = $request->getQueryParams();
        if (!isset($params['editara'])) {
            return false;
        }

        // only do something on POST requests
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        // only allow application/json content type
        if($request->getHeaderLine('Content-Type') !== 'application/json') {
            throw new UnauthorizedException('Content-Type must be application/json to save stuff with editara');
        }

        // backend user required
        $user = $this->context->getAspect('backend.user');
        if (!$user instanceof UserAspect) {
            throw new \RuntimeException('UserAspect not set in context');
        }

        if (!$user->isLoggedIn()) {
            throw new \RuntimeException('not logged in');
        }

        if ($user->isAdmin()) {
            return true;
        }

        $beUser = $GLOBALS['BE_USER'] ?? null;
        if (!$beUser instanceof BackendUserAuthentication) {
            throw new UnauthorizedException('No $GLOBALS[\'BE_USER\'] available');
        }

        // check permissions of user on page
        $pageInformation = $this->getPageInformation($request);

        if (!$beUser->isInWebMount($pageInformation->getId())) {
            throw new UnauthorizedException('No permission to access this page');
        }

        if (!$beUser->doesUserHaveAccess($pageInformation->getPageRecord(), Permission::CONTENT_EDIT)) {
            throw new UnauthorizedException('No permission to edit content on this page');
        }

        return true;
    }

    private function getPageInformation(ServerRequestInterface $request): PageInformation
    {
        $frontendPageInformation = $request->getAttribute('frontend.page.information');
        if (!$frontendPageInformation instanceof PageInformation) {
            throw new \RuntimeException('No frontend page information available');
        }
        return $frontendPageInformation;
    }

//    private function renderOnlyInputFields(): string
//    {
//        $reflectionClass = new ReflectionClass($this->pageRenderer);
//        $assetIncludes = $reflectionClass->getMethod('renderJavaScriptAndCss')->invoke($this->pageRenderer);
//        $assetIncludes = implode('', $assetIncludes); // TODO renderJavaScriptAndCss is not public! this will not work
//
//        $editable = '';
//        foreach(self::$editableResults as $editableResult) {
//            $record = $editableResult->editable->record;
//            $fullType = $record->getFullType();
//            $label = '<strong>' . htmlspecialchars($fullType . '[' . $record->getUid() . ']' . $editableResult->editable->field) . ':</strong><br>&nbsp;&nbsp;&nbsp;';
//            $html = $label . $editableResult->html . '<br>';
//            $editable .= '<div style="padding: 5px; border: 1px solid black;">' . $html . '</div>';
//        }
//        return $assetIncludes . $editable;
//    }
}
