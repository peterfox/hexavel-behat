<?php

namespace Hexavel;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use BehatResources\Context\ResourceContext;
use BehatResources\ResourceBuilder;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\MatchersProviderInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

abstract class HexavelContext extends PageObjectContext implements MinkAwareContext, ResourceContext, MatchersProviderInterface
{
    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var Page
     */
    protected $currentPage;

    /**
     * @var ResourceBuilder
     */
    protected $resourceBuilder;

    /**
     * @var string
     */
    protected $homePage = 'Home';

    /**
     * @var string
     */
    protected $pageResources = 'Page';

    /**
     * @var bool
     */
    protected $supportPlural = true;

    /**
     * @var array
     */
    private $minkParameters;

    public function getSession()
    {
        return $this->mink->getSession();
    }

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @param ResourceBuilder $builder
     */
    public function setResourceBuilder(ResourceBuilder $builder)
    {
        $this->resourceBuilder = $builder;
    }

    /**
     * @param $type
     * @param $identifier
     * @return array
     */
    public function getResource($type, $identifier)
    {
        return $this->resourceBuilder->getLoader()->load($type, $identifier);
    }

    /**
     * @param string $type
     * @param string $identifier
     * @return array
     */
    public function getResourceObject($type, $identifier)
    {
        return $this->resourceBuilder->build($type, $identifier);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getResourceClass($type)
    {
        return $this->resourceBuilder->getClassResolver()->getClassForType($type);
    }

    /**
     * @param string $type
     * @param string $identifier
     * @return array
     */
    public function getPersistedResourceObject($type, $identifier)
    {
        return $this->resourceBuilder->persist($type, $identifier);
    }

    /**
     * Sets parameters provided for Mink.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return Page
     */
    public function openPage($name, $arguments = [])
    {
        $this->currentPage = $this->getPage($name)->open($arguments);
        expect($this->currentPage->isOpen())->toBe(true);
        return $this->currentPage;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Opens homepage.
     *
     * @Given /^(?:|I )am on (?:|the )homepage$/
     * @When /^(?:|I )go to (?:|the )homepage$/
     */
    public function visitHomepage()
    {
        $this->openPage($this->homePage);
    }

    /**
     * Checks, that current page is the homepage.
     *
     * @Then /^(?:|I )should be on (?:|the )homepage$/
     */
    public function assertHomepage()
    {
        expect($this->getPage($this->homePage))->toBeOpen();
    }

    /**
     * @Transform table:parameter,value
     */
    public function castParametersTable(TableNode $parametersTable)
    {
        $parameters = [];
        foreach ($parametersTable->getHash() as $parameterHash) {
            $parameters[$parameterHash['parameter']] = $parameterHash['value'];
        }

        return $parameters;
    }

    /**
     * Opens specified page.
     *
     * @Given /^(?:|I )am on "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)"$/
     * @Given /^(?:|I )am on "(?P<page>[^"]+)" with the parameters:$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)" with the parameters:$/
     * @param string $page
     * @param array $parameters
     */
    public function visit($page, $parameters = [])
    {
        $this->openPage($page, $parameters);
    }

    /**
     * @Given /^(?:|I )am on "(?P<page>[^"]+)" with the parameters "(?P<parametersIdentifier>[^"]+)"$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)" with the parameters "(?P<parametersIdentifier>[^"]+)"$/
     * @param $page
     * @param $parameters
     */
    public function visitWithResource($page, $parameters)
    {
        $parameters = $this->getResource($this->pageResources, $parameters);
        $this->openPage($page, $parameters);
    }

    /**
     * Opens specified page.
     *
     * @Then /^(?:|I )should be on "(?P<page>[^"]+)"$/
     * @param $page
     */
    public function seeing($page)
    {
        expect($this->getPage($page))->toBeOpen();
    }

    /**
     * Checks, that page contains specified text.
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsText($text)
    {
        expect($this->getCurrentPage())->toHaveContent($text);
    }

    /**
     * Checks, that page doesn't contain specified text.
     *
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageNotContainsText($text)
    {
        expect($this->getCurrentPage())->toNotHaveContent($text);
    }

    /**
     * Clicks link with specified id|title|alt|text.
     *
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function clickLink($link)
    {
        $this->getCurrentPage()->clickLink($link);
    }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)"$/
     */
    public function pressButton($button)
    {
        $this->getCurrentPage()->pressButton($button);
    }

    /**
     * @Given /^there is a (?P<resource>[^"]*) called "(?P<name>[^"]*)"$/
     * @Given /^I (?: |have |own |am )a (?P<resource>[^"]*) called "(?P<name>[^"]*)"$/
     * @param string $resource
     * @param string $name
     */
    public function thereIsAResourceCalled($resource, $name)
    {
        $this->getPersistedResourceObject($resource, $name);
    }

    /**
     * @Given /^there are multiple (?P<resource>[^"]*) called:$/
     * @param string $resource
     * @param TableNode $tableNode
     */
    public function thereAreMultipleResourcesCalled($resource, TableNode $tableNode)
    {
        if ($this->supportPlural) {
            $resource = str_singular($resource);
        }

        foreach ($tableNode->getHash() as $resourceHash) {
            $this->getPersistedResourceObject($resource, $resourceHash['name']);
        }
    }

    /**
     * Reloads current page.
     *
     * @When /^(?:|I )reload the page$/
     */
    public function reload()
    {
        $this->getSession()->reload();
    }

    /**
     * Moves backward one page in history.
     *
     * @When /^(?:|I )move backward one page$/
     */
    public function back()
    {
        $this->getSession()->back();
    }

    /**
     * Moves forward one page in history
     *
     * @When /^(?:|I )move forward one page$/
     */
    public function forward()
    {
        $this->getSession()->forward();
    }

    public function getMatchers()
    {
        return [
            'haveContent' => function (Page $page, $expectedContent) {
                if (!$page->hasContent($expectedContent)) {
                    throw new FailureException(sprintf(
                        'Page Object with content "%s"',
                        $expectedContent
                    ));
                }

                return true;
            }
        ];
    }

    public static function getAcceptedSnippetType()
    {
        return 'regex';
    }
}