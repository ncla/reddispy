<?php

namespace App\Scraper\Reddit;

trait UserAgentGenerator
{
    /**
     * Do: version, app name, commit number, created by, request by (fork)
     * Needed to avoid rate-limit due to no identifiable user-agent
     *
     * https://github.com/reddit-archive/reddit/wiki/API
     *
     * "Change your client's User-Agent string to something unique and descriptive, including the target platform,
     * a unique application identifier, a version string, and your username as contact information, in the following
     * format: <platform>:<app ID>:<version string> (by /u/<reddit username>)"
     *
     * @return string
     */
    protected function createUserAgent()
    {
        // PHP_OS_FAMILY actually contains the platform on which PHP was built.
        return PHP_OS_FAMILY . ':' . 'ReddiSpy' . ':' . $this->getComposerVersion() . ' (by /u/iamncla)';
    }

    private function getComposerVersion()
    {
        $composerContents = file_get_contents(base_path() . '/composer.json');

        if ($composerContents === false) {
            return 'undefined';
        }

        $composerJsonParsed = json_decode($composerContents);

        if (!isset($composerJsonParsed->version)) {
            return 'undefined';
        }

        return $composerJsonParsed->version;
    }
}