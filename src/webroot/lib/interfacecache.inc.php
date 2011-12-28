<?php
/**
 * Copyright 2011 Clearspring Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
* Simple mock tool that abstractly wraps a given interface with a layer that caches all output to local 
* files, useful for testing or offline dev.  Instantiate this and pass it your instance, and it will wrap
* all calls (other than those you blacklist).
*/
class InterfaceCache {

  /**
  * @param wrappedObj An instance, of any class, to wrap with the file-based cache
  * @param cacheDir Where to write cache files (each unique method call sig gets a file)
  * @param methodBlacklist A set of method names to NOT wrap
  */
  public function __construct($wrappedObj, $cacheDir, $methodBlacklist) {
    $this->wrappedObj = $wrappedObj;
    $this->classRefl = new ReflectionClass(__CLASS__);
    $this->wrappedObjRefl = new ReflectionObject($wrappedObj);
    $this->cacheDir = $cacheDir;
    $this->blacklist = $methodBlacklist;
  }

  // Stuff that might be useful...
  
  public function deleteMethodCache($name, $args = NULL) {
    // TODO
  }

  public function deleteCache() {
    // TODO
  }
  
  /** 
  * END public interface-----------------------------
  */

  private $wrappedObj;
  private $classRefl;
  private $wrappedObjRefl;
  private $cacheDir;
  private $blacklist;

  public function __call($name, $arguments) {
    return $this->wrapCall($name, $arguments);
  }

  private function getCacheFileName($methodName, $args) {
    //echo "CWD: " . getcwd() . "<br/>";
    $name = $this->cacheDir . "/" . $methodName . "-" . base64_encode(implode("&", $args));
    return $name;
  }
  
  private function wrapCall($methodName, $args, $skipCache = FALSE) {
    $wrappedMethod = $this->wrappedObjRefl->getMethod($methodName);
    
    // Blacklisted?
    $skipCache = FALSE;
    if (($this->blacklist != NULL) && (in_array($methodName, $this->blacklist))) {
      $skipCache = TRUE;
    }
    if (!$skipCache) {

      // Check the cache, then invoke as needed
      $cacheFile = $this->getCacheFileName($methodName, $args);
      if (file_exists($cacheFile)) {
        return unserialize(base64_decode(file_get_contents($cacheFile)));
      } else {
        $result = $wrappedMethod->invokeArgs($this->wrappedObj, $args);
        file_put_contents($cacheFile, base64_encode(serialize($result)));
        return $result;  
      }
    } else {
      
      // Blacklisted, just invoke
      return $wrappedMethod->invokeArgs($this->wrappedObj, $args);
    }
  }
}
